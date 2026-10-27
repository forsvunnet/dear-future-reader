<?php

declare(strict_types=1);

use Domain\Dfr\Actions\ReadGitCommitChangesWithGit;
use Domain\Dfr\Actions\ReadGitCommitHeaderWithGit;
use Domain\Dfr\Data\FileChange;
use Domain\Dfr\Data\GitCommit;
use Symfony\Component\Process\Process;

/**
 * Runs git commands in the folder and commits all files with the message.
 *
 * @param  list<list<string>>  $commands
 */
function commitAll(string $folder, string $message, array $commands = []): void
{
    foreach ([...$commands, ['add', '-A'], ['-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-q', '--allow-empty', '-m', $message]] as $arguments) {
        (new Process(['git', ...$arguments], $folder))->mustRun();
    }
}

it('reads the author, the subject and the body', function () {
    $repository = $this->makeRepository();
    commitAll($repository, "Add the plan\n\nThe plan has two steps.\nBoth are small.");

    $header = (new ReadGitCommitHeaderWithGit)(new GitCommit($this->headHash($repository), $repository));

    expect($header?->author)->toBe('Test')
        ->and($header?->subject)->toBe('Add the plan')
        ->and($header?->body)->toBe("The plan has two steps.\nBoth are small.");
});

it('reads the path, the counts and the patch of each file', function () {
    $repository = $this->makeRepository();
    file_put_contents("$repository/old.txt", "one\ntwo\nthree\n");
    commitAll($repository, 'first files');

    file_put_contents("$repository/my notes.md", "# Notes\n");
    file_put_contents("$repository/image.bin", "\0\1\2");
    commitAll($repository, 'second', [['mv', 'old.txt', 'new.txt']]);
    file_put_contents("$repository/new.txt", "one\ntwo\nthree\nfour\n");
    commitAll($repository, 'third');

    $read = new ReadGitCommitChangesWithGit;
    $rename = $read(new GitCommit(trim((new Process(['git', 'rev-parse', 'HEAD~1'], $repository))->mustRun()->getOutput()), $repository));
    $change = $read(new GitCommit($this->headHash($repository), $repository));

    expect(array_map(fn (FileChange $change) => [$change->path, $change->oldPath, $change->added, $change->deleted, $change->patch], $rename))->toBe([
        ['image.bin', null, null, null, ''],
        ['my notes.md', null, 1, 0, "@@ -0,0 +1 @@\n+# Notes"],
        ['new.txt', 'old.txt', 0, 0, ''],
    ])->and($change[0]->patch)->toBe("@@ -1,3 +1,4 @@\n one\n two\n three\n+four");
});

it('reads nothing for a commit that the repository does not have', function () {
    $repository = $this->makeRepository();
    $commit = new GitCommit(str_repeat('a', 40), $repository);

    expect((new ReadGitCommitHeaderWithGit)($commit))->toBeNull()
        ->and((new ReadGitCommitChangesWithGit)($commit))->toBe([]);
});

it('reads nothing when the repository folder is gone', function () {
    $commit = new GitCommit(str_repeat('a', 40), $this->makeRepository().'/gone');

    expect((new ReadGitCommitHeaderWithGit)($commit))->toBeNull()
        ->and((new ReadGitCommitChangesWithGit)($commit))->toBe([]);
});

it('ignores changes to whitespace', function () {
    $repository = $this->makeRepository();
    file_put_contents("$repository/indent.php", "if (true) {\necho 1;\n}\n");
    file_put_contents("$repository/other.txt", "one\n");
    commitAll($repository, 'first files');
    file_put_contents("$repository/indent.php", "if (true) {\n    echo 1;\n}\n");
    file_put_contents("$repository/other.txt", "two\n");
    commitAll($repository, 'indent and change');

    $changes = (new ReadGitCommitChangesWithGit)(new GitCommit($this->headHash($repository), $repository));

    expect(array_map(fn (FileChange $change) => [$change->path, $change->patch], $changes))->toBe([
        ['other.txt', "@@ -1 +1 @@\n-one\n+two"],
    ]);
});
