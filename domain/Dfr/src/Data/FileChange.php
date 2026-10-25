<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * The change to one file in a commit.
 */
readonly class FileChange
{
    /**
     * @param  ?string  $oldPath  The path before a rename.
     * @param  ?int  $added  The number of added lines, or null for a binary file.
     * @param  ?int  $deleted  The number of deleted lines, or null for a binary file.
     * @param  string  $patch  The lines of the unified diff from the first "@@", or empty when the content did not change.
     */
    public function __construct(
        public string $path,
        public ?string $oldPath,
        public ?int $added,
        public ?int $deleted,
        public string $patch,
    ) {}
}
