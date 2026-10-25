<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

use Domain\Dfr\Data\FileChange;
use Domain\Dfr\Data\GitCommit;

/**
 * Reads the changes to each file in a commit.
 */
interface ReadGitCommitChanges
{
    /**
     * @return list<FileChange> empty when the repository no longer has the commit.
     */
    public function __invoke(GitCommit $commit): array;
}
