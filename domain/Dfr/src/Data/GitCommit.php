<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class GitCommit
{
    /**
     * @param  string  $hash  The full hash.
     * @param  string  $repository  The path of the top folder of the git repository.
     */
    public function __construct(
        public string $hash,
        public string $repository,
    ) {}
}
