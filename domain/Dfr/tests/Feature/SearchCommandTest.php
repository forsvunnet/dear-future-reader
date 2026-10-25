<?php

declare(strict_types=1);

it('finds the lines that contain the text, with case ignored', function () {
    $first = $this->init('first');
    $second = $this->init('second');
    $this->dfr([$first, 'write', 'plan.md'], "Build the Login page\nThen test it\n");
    $this->dfr([$second, 'write', 'notes.md'], "nothing here\nlogin works\n");

    expect($this->dfr(['search', 'login'])->getOutput())
        ->toBe("$first\tplan.md\t1\tBuild the Login page\n$second\tnotes.md\t2\tlogin works\n");
});

it('skips archived files and the data files of the thing', function () {
    $id = $this->init('login');
    $this->dfr([$id, 'write', 'old.md', 'login']);
    $this->dfr([$id, 'files:archive', 'old.md']);

    expect($this->dfr(['search', 'login'])->getOutput())->toBe('');
});

it('shows the matches as JSON', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'login page']);

    expect(json_decode($this->dfr(['search', 'login', '--json'])->getOutput(), true))
        ->toBe([['id' => $id, 'file' => 'plan.md', 'line' => 1, 'text' => 'login page']]);
});

it('shows nothing and exits with 0 when nothing matches', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'text']);
    $process = $this->dfr(['search', 'login']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('');
});

it('fails on an empty text', function () {
    $process = $this->dfr(['search', ' ']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('Give a text to find.');
});
