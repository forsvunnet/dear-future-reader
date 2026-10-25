<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class GitCommitResult
{
    public function __construct(
        public ?GitCommit $commit,
        public ?string $error,
    ) {}
}
