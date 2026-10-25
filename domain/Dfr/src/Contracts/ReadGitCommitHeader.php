<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

use Domain\Dfr\Data\GitCommit;
use Domain\Dfr\Data\GitCommitHeader;

/**
 * Reads the author, the time and the message of a commit.
 */
interface ReadGitCommitHeader
{
    /**
     * @return ?GitCommitHeader null when the repository no longer has the commit.
     */
    public function __invoke(GitCommit $commit): ?GitCommitHeader;
}
