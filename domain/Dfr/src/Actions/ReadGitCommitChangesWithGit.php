<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\ReadGitCommitChanges;
use Domain\Dfr\Data\FileChange;
use Domain\Dfr\Data\GitCommit;
use Symfony\Component\Process\Process;

/**
 * Reads the changes to each file in a commit with the git command.
 *
 * The paths and line counts come from --numstat -z, which gives each path exactly.
 * The patches come from a second call. Both list the files in the same order.
 */
class ReadGitCommitChangesWithGit implements ReadGitCommitChanges
{
    public function __invoke(GitCommit $commit): array
    {
        // A merge commit shows the changes against its first parent, like a normal commit.
        $options = ['show', '--format=', '--no-color', '--find-renames', '--diff-merges=first-parent'];
        $numstat = new Process(['git', ...$options, '--numstat', '-z', '--end-of-options', $commit->hash], $commit->repository);
        $numstat->run();
        $patch = new Process(['git', ...$options, '--patch', '--no-ext-diff', '--no-textconv', '--end-of-options', $commit->hash], $commit->repository);
        $patch->run();

        if (! $numstat->isSuccessful() || ! $patch->isSuccessful()) {
            return [];
        }

        $patches = $this->patches($patch->getOutput());
        $changes = [];

        foreach ($this->numstat($numstat->getOutput()) as $index => [$path, $oldPath, $added, $deleted]) {
            $changes[] = new FileChange($path, $oldPath, $added, $deleted, $patches[$index] ?? '');
        }

        return $changes;
    }

    /**
     * @return list<array{string, ?string, ?int, ?int}> The path, the old path, the added and the deleted lines of each file.
     */
    public function numstat(string $output): array
    {
        // Each file is "added\tdeleted\tpath\0". A rename is "added\tdeleted\t\0old path\0new path\0". A binary file has "-" for the counts.
        $parts = explode("\0", $output);
        $files = [];

        for ($i = 0; $i < count($parts) && $parts[$i] !== ''; $i++) {
            [$added, $deleted, $path] = explode("\t", $parts[$i], 3) + ['', '', ''];
            $oldPath = null;

            if ($path === '') {
                $oldPath = $parts[$i + 1] ?? '';
                $path = $parts[$i + 2] ?? '';
                $i += 2;
            }

            $files[] = [$path, $oldPath, $added === '-' ? null : (int) $added, $deleted === '-' ? null : (int) $deleted];
        }

        return $files;
    }

    /**
     * @return list<string> The lines from the first "@@" of each file, or empty when the file has no hunk.
     */
    public function patches(string $output): array
    {
        // A line of content starts with "+", "-" or " ", so only a file header starts with "diff --git ".
        $files = preg_split('/^(?=diff --git )/m', $output, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_map(function (string $file) {
            $start = strpos($file, "\n@@");

            return $start === false ? '' : rtrim(substr($file, $start + 1), "\n");
        }, $files);
    }
}
