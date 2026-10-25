<?php

declare(strict_types=1);

it('makes a thing and shows only its ID', function () {
    $process = $this->dfr(['init', 'login page']);
    $id = trim($process->getOutput());

    expect($process->getExitCode())->toBe(0)
        ->and($id)->toMatch('/^[2-9a-hjkmnp-z]{3}$/')
        ->and(json_decode((string) file_get_contents($this->thingFolder($id).'/.thing.json'), true))
        ->toBe(['name' => 'login page', 'files' => []]);
});

it('fails on an empty name', function () {
    $process = $this->dfr(['init', ' ']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getOutput())->toBe('')
        ->and(is_dir("$this->home/.dfr"))->toBeFalse();
});

it('uses the things_path from the config file', function () {
    mkdir("$this->home/.config/dfr", 0777, true);
    file_put_contents("$this->home/.config/dfr/config.json", '{"things_path": "~/elsewhere"}');

    $id = $this->init();

    expect(is_file("$this->home/elsewhere/$id/.thing.json"))->toBeTrue();
});
