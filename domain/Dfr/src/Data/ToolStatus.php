<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * Whether a command is installed, where and in which version.
 */
readonly class ToolStatus
{
    /**
     * @param  ?string  $path  Null when the command is not installed.
     * @param  ?string  $version  The first line of `--version`, or null when the command is not installed or gives no version.
     */
    public function __construct(
        public string $name,
        public ?string $path,
        public ?string $version,
    ) {}
}
