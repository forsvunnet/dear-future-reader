<?php

declare(strict_types=1);

it('ticks each matching item, in all lists and files without --list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [ ] Define dependencies\n- [ ] Install packages\n");
    $this->dfr([$id, 'write', 'release.md'], "Release\n* [ ] Run build command\n");

    $process = $this->dfr([$id, 'todo', 'tick', 'DEPENDENCIES', 'build']);

    expect($process->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n- [x] Define dependencies\n- [ ] Install packages\n")
        ->and(file_get_contents($this->thingFolder($id).'/release.md'))->toBe("Release\n* [x] Run build command\n");
});

it('picks the equal item over items that only contain the text', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [ ] Install packages\n- [ ] Install\n");
    $this->dfr([$id, 'todo', 'tick', 'install']);

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n- [ ] Install packages\n- [x] Install\n");
});

it('looks only in the given list with --list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "A\n- [ ] build\nB\n- [ ] build\n");
    $this->dfr([$id, 'todo', 'tick', 'build', '--list', 'A']);

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("A\n- [x] build\nB\n- [ ] build\n");
});

it('fails and changes nothing when one argument matches no item or more than one', function () {
    $id = $this->init();
    $content = "Setup\n- [ ] Install packages\n- [ ] Verify installation\n- [ ] Run build command\n";
    $this->dfr([$id, 'write', 'plan.md'], $content);

    $process = $this->dfr([$id, 'todo', 'tick', 'build', 'install', 'deploy']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toBe(
            "More than one todo item matches \"install\": \"Install packages\", \"Verify installation\".\n"
            ."No todo item matches \"deploy\".\n"
        )
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe($content);
});
