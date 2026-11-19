<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\ThingsRoot;

/**
 * Finds the files that the QMD index takes: the .md and .log files in the folder of each thing. This is the mask of MakeQmdIndex.
 */
class FindQmdFiles
{
    public function __construct(
        private ThingsRoot $thingsRoot,
    ) {}

    /**
     * @return list<string> The full paths.
     */
    public function __invoke(): array
    {
        return [...(glob($this->thingsRoot->path.'/*/*.md') ?: []), ...(glob($this->thingsRoot->path.'/*/*.log') ?: [])];
    }
}
