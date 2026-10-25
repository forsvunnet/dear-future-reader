<?php

declare(strict_types=1);

it('unticks each matching item', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [x] one\n- [X] two\n- [x] three\n");

    $process = $this->dfr([$id, 'todo', 'untick', 'one', 'two', '--list', 'Setup']);

    expect($process->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n- [ ] one\n- [ ] two\n- [x] three\n");
});

it('fails and changes nothing when one argument matches no item', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [x] one\n");

    $process = $this->dfr([$id, 'todo', 'untick', 'one', 'two']);

    expect($process->getExitCode())->toBe(1)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n- [x] one\n");
});
