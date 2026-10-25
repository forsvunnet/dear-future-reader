<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Closure;
use Domain\Dfr\Data\Thing;
use Illuminate\Filesystem\LockableFile;

/**
 * Changes a context file while the thing is locked, and adds the file to the end of the order.
 * The lock stops two sessions from losing each other's changes.
 */
class ChangeFile
{
    public function __construct(
        private ReadThing $readThing,
        private SaveThing $saveThing,
        private WriteFileAtomically $writeFile,
    ) {}

    /**
     * @param  Closure(string): string  $change  Gets the old content ("" for a new file) and returns the new content.
     */
    public function __invoke(Thing $thing, string $fileName, Closure $change): void
    {
        $lock = new LockableFile("$thing->folder/.lock", 'c+');
        $lock->getExclusiveLock(true);

        try {
            $path = "$thing->folder/$fileName";
            $oldContent = is_file($path) ? (string) file_get_contents($path) : '';

            ($this->writeFile)($path, $change($oldContent));

            // Read the thing again, because another session can have changed it before the lock.
            $current = ($this->readThing)($thing->id) ?? $thing;

            if (! in_array($fileName, $current->files, true)) {
                ($this->saveThing)(new Thing($current->id, $current->folder, $current->name, [...$current->files, $fileName]));
            }
        } finally {
            $lock->close();
        }
    }
}
