<?php

declare(strict_types=1);

it('changes the name and keeps the ID', function () {
    $id = $this->init('old name');
    $process = $this->dfr([$id, 'rename', 'new name']);

    expect($process->getExitCode())->toBe(0)
        ->and($this->dfr(['list'])->getOutput())->toBe("$id\tnew name\n");
});

it('fails on an empty name', function () {
    $id = $this->init('old name');
    $process = $this->dfr([$id, 'rename', ' ']);

    expect($process->getExitCode())->toBe(1)
        ->and($this->thingData($id)['name'])->toBe('old name');
});
