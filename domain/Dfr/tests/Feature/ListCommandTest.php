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

it('shows each child under its parent', function () {
    $b = $this->init('b');
    $a = $this->init('a');
    $child = trim($this->dfr(['init', 'child', '--parent', $b])->getOutput());
    $grandchild = trim($this->dfr(['init', 'grandchild', '--parent', $child])->getOutput());

    expect($this->dfr(['list', '--tree'])->getOutput())
        ->toBe("$a\ta\n$b\tb\n  $child\tchild\n    $grandchild\tgrandchild\n");
});

it('shows things in a circle at the top of the tree', function () {
    $a = $this->init('a');
    $b = $this->init('b');

    // A circle can only come from two sessions at the same moment, so the test writes it directly.
    foreach ([[$a, $b], [$b, $a]] as [$id, $parent]) {
        $data = $this->thingData($id);
        $data['parent'] = $parent;
        file_put_contents($this->thingFolder($id).'/.thing.json', json_encode($data));
    }

    expect($this->dfr(['list', '--tree'])->getOutput())->toBe("$a\ta\n  $b\tb\n");
});

it('shows the things as JSON', function () {
    $parent = $this->init('parent');
    $child = trim($this->dfr(['init', 'child', '--parent', $parent])->getOutput());

    expect(json_decode($this->dfr(['list', '--json'])->getOutput(), true))->toBe([
        ['id' => $child, 'name' => 'child', 'parent' => $parent, 'projects' => []],
        ['id' => $parent, 'name' => 'parent', 'parent' => null, 'projects' => []],
    ]);
});

it('fails on --tree together with --json', function () {
    $this->init();
    $process = $this->dfr(['list', '--tree', '--json']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('--tree does not work together');
});

it('shows the projects of each thing', function () {
    $first = $this->makeRepository('first');
    $second = $this->makeRepository('second');
    $id = trim($this->dfr(['init', 'test'], folder: $first)->getOutput());
    $this->dfr([$id, 'git:add', 'HEAD'], folder: $second);
    $this->dfr([$id, 'git:add', 'HEAD'], folder: $first);
    $other = $this->init('other');

    expect($this->dfr(['list'])->getOutput())->toBe("$other\tother\n$id\ttest\tfirst, second\n")
        ->and(json_decode($this->dfr(['list', '--json'])->getOutput(), true))->toBe([
            ['id' => $other, 'name' => 'other', 'parent' => null, 'projects' => []],
            ['id' => $id, 'name' => 'test', 'parent' => null, 'projects' => [realpath($first), realpath($second)]],
        ]);
});
