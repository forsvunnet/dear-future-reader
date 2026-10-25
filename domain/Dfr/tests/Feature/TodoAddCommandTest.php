<?php

declare(strict_types=1);

it('adds the items to the end of the list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n- [x] one\n\nNotes\n");

    $process = $this->dfr([$id, 'todo', 'add', 'two', 'three', '--list', 'setup']);

    expect($process->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n- [x] one\n- [ ] two\n- [ ] three\n\nNotes\n");
});

it('adds to the only list without --list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup\n  * [ ] one\n");
    $this->dfr([$id, 'todo', 'add', 'two']);

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Setup\n  * [ ] one\n  * [ ] two\n");
});

it('makes the list in todo.md when no list matches --list', function () {
    $id = $this->init();
    $this->dfr([$id, 'todo', 'add', 'one', 'two', '--list', 'Setup']);
    $this->dfr([$id, 'todo', 'add', 'three', '--list', 'Release']);

    expect(file_get_contents($this->thingFolder($id).'/todo.md'))
        ->toBe("## Setup\n\n- [ ] one\n- [ ] two\n\n## Release\n\n- [ ] three\n")
        ->and(json_decode(file_get_contents($this->thingFolder($id).'/.thing.json'), true)['files'])->toBe(['todo.md']);
});

it('fails without --list when the thing has no list or more than one', function (string $content, string $error) {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], $content);

    $process = $this->dfr([$id, 'todo', 'add', 'two']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toBe("$error\n")
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe($content);
})->with([
    'no list' => ["no todos\n", 'The thing has no todo list.'],
    'two lists' => ["A\n- [ ] one\nB\n- [ ] one\n", 'The thing has 2 todo lists. Give one with --list.'],
]);

it('fails when --list matches more than one list', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Setup one\n- [ ] a\nSetup two\n- [ ] b\n");

    $process = $this->dfr([$id, 'todo', 'add', 'c', '--list', 'setup']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toBe("More than one todo list matches \"setup\": \"Setup one\" in plan.md, \"Setup two\" in plan.md.\n");
});

it('fails for a thing that does not exist', function () {
    expect($this->dfr(['nope', 'todo', 'add', 'one'])->getErrorOutput())->toBe("No thing has the ID nope.\n");
});
