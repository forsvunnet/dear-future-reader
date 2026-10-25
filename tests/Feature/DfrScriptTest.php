<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('runs artisan from another folder through a symlink', function () {
    $folder = sys_get_temp_dir() . '/dfr-test-' . bin2hex(random_bytes(4));
    mkdir($folder);
    symlink(base_path('dfr'), "$folder/dfr");

    $process = new Process(["$folder/dfr", '--version'], $folder);
    $process->run();

    unlink("$folder/dfr");
    rmdir($folder);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toContain('Laravel Framework');
});

it('fails when an ID has no command', function () {
    $process = new Process([base_path('dfr'), 'abc']);
    $process->run();

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('give a command after the ID');
});
