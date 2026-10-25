<?php

declare(strict_types=1);

it('lists the things by name, with their status', function () {
    $second = $this->init('second');
    $first = $this->init('first');
    $this->dfr([$second, 'status', 'in progress']);

    expect($this->dfr(['list'])->getOutput())->toBe("$first\tfirst\t\n$second\tsecond\tin progress\n");
});

it('shows nothing when there are no things', function () {
    $process = $this->dfr(['list']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('');
});

it('lists only the things with the exact status', function () {
    $done = $this->init('done thing');
    $other = $this->init('other thing');
    $this->dfr([$done, 'status', 'done']);
    $this->dfr([$other, 'status', 'done soon']);

    expect($this->dfr(['list', '--status', 'done'])->getOutput())->toBe("$done\tdone thing\tdone\n");
});

it('shows each child under its parent', function () {
    $b = $this->init('b');
    $a = $this->init('a');
    $child = trim($this->dfr(['init', 'child', '--parent', $b])->getOutput());
    $grandchild = trim($this->dfr(['init', 'grandchild', '--parent', $child])->getOutput());

    expect($this->dfr(['list', '--tree'])->getOutput())
        ->toBe("$a\ta\t\n$b\tb\t\n  $child\tchild\t\n    $grandchild\tgrandchild\t\n");
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

    expect($this->dfr(['list', '--tree'])->getOutput())->toBe("$a\ta\t\n  $b\tb\t\n");
});

it('shows the things as JSON', function () {
    $parent = $this->init('parent');
    $child = trim($this->dfr(['init', 'child', '--parent', $parent])->getOutput());
    $this->dfr([$child, 'status', 'new']);

    expect(json_decode($this->dfr(['list', '--json'])->getOutput(), true))->toBe([
        ['id' => $child, 'name' => 'child', 'status' => 'new', 'parent' => $parent],
        ['id' => $parent, 'name' => 'parent', 'status' => null, 'parent' => null],
    ]);
});

it('fails on --tree together with --status or --json', function (string $option) {
    $this->init();
    $process = $this->dfr(['list', '--tree', $option]);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('--tree does not work together');
})->with(['--status=done', '--json']);
