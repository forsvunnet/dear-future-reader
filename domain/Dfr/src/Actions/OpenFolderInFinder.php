<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\OpenFolder;
use Symfony\Component\Process\Process;

/**
 * Opens a folder in Finder with the open command of macOS.
 */
class OpenFolderInFinder implements OpenFolder
{
    // ponytail: macOS only. On Linux, use xdg-open in a second implementation.
    public function __invoke(string $folder): void
    {
        (new Process(['open', '--', $folder]))->run();
    }
}
