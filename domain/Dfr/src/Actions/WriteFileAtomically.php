<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use RuntimeException;

/**
 * Writes a temporary file and renames it, so a reader never sees a half-written file.
 * The temporary name starts with ".", so it is never seen as a context file.
 */
class WriteFileAtomically
{
    public function __invoke(string $path, string $content): void
    {
        $temporaryPath = dirname($path).'/.'.basename($path).'.'.bin2hex(random_bytes(4)).'.tmp';

        if (file_put_contents($temporaryPath, $content) === false) {
            throw new RuntimeException("Could not write $temporaryPath.");
        }

        if (! rename($temporaryPath, $path)) {
            unlink($temporaryPath);

            throw new RuntimeException("Could not write $path.");
        }
    }
}
