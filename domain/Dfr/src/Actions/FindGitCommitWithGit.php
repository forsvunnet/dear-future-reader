<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\FindGitCommit;
use Domain\Dfr\Contracts\FindGitRepository;
use Domain\Dfr\Data\GitCommit;
use Domain\Dfr\Data\GitCommitResult;
use Symfony\Component\Process\Process;

/**
 * Finds a commit with the git command.
 */
class FindGitCommitWithGit implements FindGitCommit
{
    public function __construct(
        private FindGitRepository $findGitRepository,
    ) {}

    public function __invoke(string $folder, string $revision): GitCommitResult
    {
        $repository = ($this->findGitRepository)($folder);

        if ($repository === null) {
            return new GitCommitResult(null, "$folder is not in a git repository.");
        }

        // --end-of-options stops a revision that starts with "-" from being read as an option.
        $hash = new Process(['git', 'rev-parse', '--verify', '--quiet', '--end-of-options', "$revision^{commit}"], $folder);
        $hash->run();

        if (! $hash->isSuccessful()) {
            return new GitCommitResult(null, "The git repository has no commit $revision.");
        }

        return new GitCommitResult(new GitCommit(trim($hash->getOutput()), $repository), null);
    }
}
