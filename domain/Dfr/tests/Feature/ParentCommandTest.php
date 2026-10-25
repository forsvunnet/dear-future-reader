<?php

declare(strict_types=1);

it('makes the thing a child of another thing', function () {
    $parent = $this->init('parent');
    $child = $this->init('child');
    $process = $this->dfr([$child, 'parent', $parent]);

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($child)['parent'])->toBe($parent);
});

it('fails on the thing itself', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'parent', $id]);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('cannot be the parent')
        ->and($this->thingData($id)['parent'])->toBeNull();
});

it('fails on a child of the thing at any level', function () {
    $top = $this->init('top');
    $child = trim($this->dfr(['init', 'child', '--parent', $top])->getOutput());
    $grandchild = trim($this->dfr(['init', 'grandchild', '--parent', $child])->getOutput());
    $process = $this->dfr([$top, 'parent', $grandchild]);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain("$grandchild is $top or a child of $top")
        ->and($this->thingData($top)['parent'])->toBeNull();
});

it('fails on a parent that does not exist', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'parent', 'zzz']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('No thing has the ID zzz.');
});

it('removes the parent', function () {
    $parent = $this->init('parent');
    $child = trim($this->dfr(['init', 'child', '--parent', $parent])->getOutput());
    $process = $this->dfr([$child, 'parent:remove']);

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($child)['parent'])->toBeNull();
});
