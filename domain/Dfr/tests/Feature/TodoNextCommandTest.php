<?php

declare(strict_types=1);

it('shows the first item that is not ticked', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [x] one\n- [ ] <two>\n- [ ] three\n");

    $process = $this->dfr([$id, 'todo', 'next']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe("<two>\n");
});

it('needs --list when the thing has more than one list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [ ] one\n\nBefore the release:\n- [ ] two\n");

    $without = $this->dfr([$id, 'todo', 'next']);
    $with = $this->dfr([$id, 'todo', 'next', '--list', 'release']);

    expect($without->getExitCode())->toBe(1)
        ->and($without->getErrorOutput())->toBe("The thing has 2 todo lists. Give one with --list.\n")
        ->and($with->getOutput())->toBe("two\n");
});

it('shows nothing when all items are ticked', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [x] one\n");

    $process = $this->dfr([$id, 'todo', 'next']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('')
        ->and($process->getErrorOutput())->toBe("All items in the list are ticked.\n");
});

it('fails without a todo command', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'todo']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toBe("dfr: give a todo command, like add, tick or next.\n");
});
