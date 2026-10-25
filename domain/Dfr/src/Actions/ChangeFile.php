<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Closure;
use Domain\Dfr\Data\Thing;

/**
 * Changes a context file while the thing is locked, and adds the file to the end of the order.
 */
class ChangeFile
{
    public function __construct(
        private LockThing $lockThing,
        private WriteFileAtomically $writeFile,
        private AddToOrder $addToOrder,
    ) {}

    /**
     * @param  Closure(string): string  $change  Gets the old content ("" for a new file) and returns the new content.
     */
    public function __invoke(Thing $thing, string $fileName, Closure $change): void
    {
        ($this->lockThing)($thing, function () use ($thing, $fileName, $change) {
            $path = "$thing->folder/$fileName";
            $oldContent = is_file($path) ? (string) file_get_contents($path) : '';

            ($this->writeFile)($path, $change($oldContent));
            ($this->addToOrder)($thing, $fileName);
        });
    }
}
