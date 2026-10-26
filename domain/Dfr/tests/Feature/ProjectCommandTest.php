<?php

declare(strict_types=1);

it('stores the top folder of the repository of the current folder', function () {
    $repository = $this->makeRepository();
    mkdir("$repository/sub");
    $id = $this->init();
    $process = $this->dfr([$id, 'project:add'], folder: "$repository/sub");

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($id)['projects'])->toBe([realpath($repository)]);
});

it('stores the repository of a given path only once', function () {
    $repository = $this->makeRepository();
    $id = $this->init();
    $this->dfr([$id, 'project:add', $repository]);
    $this->dfr([$id, 'project:add', $repository]);

    expect($this->thingData($id)['projects'])->toBe([realpath($repository)]);
});

it('fails on a path outside a git repository', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'project:add', sys_get_temp_dir()]);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('is not in a git repository');
});

it('shows an added project in the list of projects', function () {
    $repository = $this->makeRepository();
    $id = $this->init();
    $this->dfr([$id, 'project:add', $repository]);

    expect(json_decode($this->dfr(['list', '--json'])->getOutput(), true)[0]['projects'])->toBe([realpath($repository)]);
});

it('removes a project', function () {
    $repository = $this->makeRepository();
    $id = $this->init();
    $this->dfr([$id, 'project:add', $repository]);
    $process = $this->dfr([$id, 'project:remove', $repository]);

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($id)['projects'])->toBe([]);
});

it('fails to remove a project that was not added', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'project:remove', '/no/such/project']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The thing has no project /no/such/project.');
});
