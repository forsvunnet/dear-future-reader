<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\ReadGitCommitHeader;
use Domain\Dfr\Data\GitCommit;
use Domain\Dfr\Data\GitCommitHeader;
use Symfony\Component\Process\Process;

/**
 * Reads the author, the time and the message of a commit with the git command.
 */
class ReadGitCommitHeaderWithGit implements ReadGitCommitHeader
{
    public function __invoke(GitCommit $commit): ?GitCommitHeader
    {
        // %x00 separates the fields, because a message can hold any other character.
        $process = new Process(['git', 'show', '--no-patch', '--no-color', '--format=%an%x00%cI%x00%s%x00%b', '--end-of-options', $commit->hash], $commit->repository);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        [$author, $time, $subject, $body] = explode("\0", $process->getOutput(), 4) + ['', '', '', ''];

        return new GitCommitHeader($commit, $author, $time, $subject, trim($body));
    }
}
