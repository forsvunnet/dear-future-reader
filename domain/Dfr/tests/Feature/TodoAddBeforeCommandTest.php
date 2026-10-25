<?php

declare(strict_types=1);

it('adds the items before the matching item', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [ ] Install packages\n- [ ] Run build command\n");

    $process = $this->dfr([$id, 'todo', 'add:before', 'build', 'Clear the cache', '--list', 'Setup']);

    expect($process->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))
        ->toBe("Setup\n- [ ] Install packages\n- [ ] Clear the cache\n- [ ] Run build command\n");
});

it('fails and changes nothing when the list does not exist', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [ ] one\n");

    $process = $this->dfr([$id, 'todo', 'add:before', 'one', 'two', '--list', 'Release']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toBe("No todo list matches \"Release\".\n")
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n- [ ] one\n");
});
