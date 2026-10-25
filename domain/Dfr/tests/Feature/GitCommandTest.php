<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

/**
 * Makes a git repository with one commit and returns its folder.
 */
function makeRepository(string $home): string
{
    $folder = "$home/repository";
    mkdir($folder);

    foreach ([['init', '-q'], ['-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-q', '--allow-empty', '-m', 'first']] as $arguments) {
        (new Process(['git', ...$arguments], $folder, ['HOME' => $home]))->mustRun();
    }

    return $folder;
}

function headHash(string $folder): string
{
    return trim((new Process(['git', 'rev-parse', 'HEAD'], $folder))->mustRun()->getOutput());
}

it('stores the full hash and the path of the repository', function () {
    $repository = makeRepository($this->home);
    $hash = headHash($repository);
    $id = $this->init();
    $process = $this->dfr([$id, 'git:add', substr($hash, 0, 7)], folder: $repository);

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($id)['commits'])->toBe([['hash' => $hash, 'repository' => realpath($repository)]]);
});

it('stores a commit only once', function () {
    $repository = makeRepository($this->home);
    $id = $this->init();
    $this->dfr([$id, 'git:add', 'HEAD'], folder: $repository);
    $this->dfr([$id, 'git:add', headHash($repository)], folder: $repository);

    expect($this->thingData($id)['commits'])->toHaveCount(1);
});

it('fails on a commit that is not in the repository', function () {
    $repository = makeRepository($this->home);
    $id = $this->init();
    $process = $this->dfr([$id, 'git:add', 'deadbeef'], folder: $repository);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The git repository has no commit deadbeef.')
        ->and($this->thingData($id)['commits'])->toBe([]);
});

it('fails outside a git repository', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'git:add', 'HEAD']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('is not in a git repository');
});

it('removes a commit by the start of its hash', function () {
    $repository = makeRepository($this->home);
    $id = $this->init();
    $this->dfr([$id, 'git:add', 'HEAD'], folder: $repository);
    $process = $this->dfr([$id, 'git:remove', substr(headHash($repository), 0, 7)]);

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($id)['commits'])->toBe([]);
});

it('fails to remove a commit that is not connected', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'git:remove', 'abcd']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The thing has no commit abcd.');
});

it('fails to remove with a hash of fewer than 4 characters', function () {
    $repository = makeRepository($this->home);
    $id = $this->init();
    $this->dfr([$id, 'git:add', 'HEAD'], folder: $repository);
    $process = $this->dfr([$id, 'git:remove', substr(headHash($repository), 0, 3)]);

    expect($process->getExitCode())->toBe(1)
        ->and($this->thingData($id)['commits'])->toHaveCount(1);
});
