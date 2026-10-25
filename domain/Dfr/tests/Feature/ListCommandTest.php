<?php

declare(strict_types=1);

it('lists the things by name', function () {
    $second = $this->init('second');
    $first = $this->init('first');

    expect($this->dfr(['list'])->getOutput())->toBe("$first\tfirst\n$second\tsecond\n");
});

it('shows nothing when there are no things', function () {
    $process = $this->dfr(['list']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('');
});
