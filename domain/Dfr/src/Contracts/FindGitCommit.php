<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

use Domain\Dfr\Data\GitCommitResult;

/**
 * Finds a commit in the git repository that contains a folder.
 */
interface FindGitCommit
{
    /**
     * @param  string  $revision  A hash, a short hash or another name of the commit, like HEAD.
     */
    public function __invoke(string $folder, string $revision): GitCommitResult;
}
