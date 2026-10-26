<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class Thing
{
    /**
     * @param  ?string  $parent  The ID of the parent thing.
     * @param  ?string  $repository  The path of the git repository of the folder where the thing was made.
     * @param  list<string>  $projects  The paths of more git repositories that the work is about.
     * @param  list<GitCommit>  $commits
     * @param  list<string>  $files  The filenames in their order.
     */
    public function __construct(
        public string $id,
        public string $folder,
        public string $name,
        public ?string $parent,
        public ?string $repository,
        public array $projects,
        public array $commits,
        public array $files,
    ) {}
}
