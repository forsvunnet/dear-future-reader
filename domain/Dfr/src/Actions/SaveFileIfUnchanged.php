<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Writes a context file, but only when the file on disk still has the content that the writer started from.
 * This stops a save from the Web UI from removing a change that a session made in the meantime.
 */
class SaveFileIfUnchanged
{
    public function __construct(
        private LockThing $lockThing,
        private WriteFileAtomically $writeFile,
    ) {}

    /**
     * @param  string  $oldHash  The sha1 of the content that the writer started from.
     * @return bool False when the file changed, was archived or was removed. Then nothing is written.
     */
    public function __invoke(Thing $thing, string $fileName, string $oldHash, string $content): bool
    {
        return ($this->lockThing)($thing, function () use ($thing, $fileName, $oldHash, $content) {
            $path = "$thing->folder/$fileName";

            if (! is_file($path) || sha1((string) file_get_contents($path)) !== $oldHash) {
                return false;
            }

            ($this->writeFile)($path, $content);

            return true;
        });
    }
}
