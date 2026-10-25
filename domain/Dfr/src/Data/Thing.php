<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class Thing
{
    /**
     * @param  ?string  $status  Free text, or null when the thing has no status.
     * @param  ?string  $parent  The ID of the parent thing.
     * @param  list<GitCommit>  $commits
     * @param  list<string>  $files  The filenames in their order.
     */
    public function __construct(
        public string $id,
        public string $folder,
        public string $name,
        public ?string $status,
        public ?string $parent,
        public array $commits,
        public array $files,
    ) {}
}
