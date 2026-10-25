<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\FindGitRepository;
use Symfony\Component\Process\Process;

/**
 * Finds the git repository of a folder with the git command.
 */
class FindGitRepositoryWithGit implements FindGitRepository
{
    public function __invoke(string $folder): ?string
    {
        // ponytail: a git worktree has its own top folder, so it counts as a separate repository.
        // To count it as the main repository, use the parent folder of `git rev-parse --git-common-dir`.
        $topLevel = new Process(['git', 'rev-parse', '--show-toplevel'], $folder);
        $topLevel->run();

        return $topLevel->isSuccessful() ? trim($topLevel->getOutput()) : null;
    }
}
