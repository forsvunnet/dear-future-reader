<?php

declare(strict_types=1);

it('shows nothing when the thing has no status', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'status']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('');
});

it('sets and shows the status', function () {
    $id = $this->init();
    $this->dfr([$id, 'status', 'waiting for review']);

    expect($this->dfr([$id, 'status'])->getOutput())->toBe("waiting for review\n");
});

it('removes the status with an empty text', function () {
    $id = $this->init();
    $this->dfr([$id, 'status', 'done']);
    $this->dfr([$id, 'status', '']);

    expect($this->thingData($id)['status'])->toBeNull();
});

it('keeps the other data of the thing', function () {
    $id = $this->init('my thing');
    $this->dfr([$id, 'write', 'plan.md', 'text']);
    $this->dfr([$id, 'status', 'done']);

    expect($this->thingData($id))->toMatchArray(['name' => 'my thing', 'status' => 'done', 'files' => ['plan.md']]);
});

it('fails on an unknown ID', function () {
    $process = $this->dfr(['zzz', 'status', 'done']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('No thing has the ID zzz.');
});
