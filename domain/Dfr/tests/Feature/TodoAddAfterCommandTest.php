<?php

declare(strict_types=1);

it('adds the items after the matching item, with its marker', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [ ] Install packages\n  * [x] Run build command\n");

    $process = $this->dfr([$id, 'todo', 'add:after', 'build', 'Check', 'Clean']);

    expect($process->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))
        ->toBe("Setup\n- [ ] Install packages\n  * [x] Run build command\n  * [ ] Check\n  * [ ] Clean\n");
});

it('looks in all lists without --list, and only in the given list with --list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "A\n- [ ] build\nB\n- [ ] build\n");

    $without = $this->dfr([$id, 'todo', 'add:after', 'build', 'new']);
    $with = $this->dfr([$id, 'todo', 'add:after', 'build', 'new', '--list', 'B']);

    expect($without->getErrorOutput())->toBe("More than one todo item matches \"build\": \"build\", \"build\".\n")
        ->and($with->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("A\n- [ ] build\nB\n- [ ] build\n- [ ] new\n");
});

it('fails and changes nothing when no item matches', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "A\n- [ ] one\n");

    $process = $this->dfr([$id, 'todo', 'add:after', 'two', 'three']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toBe("No todo item matches \"two\".\n")
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("A\n- [ ] one\n");
});
