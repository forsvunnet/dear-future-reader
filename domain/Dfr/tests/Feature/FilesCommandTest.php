<?php

declare(strict_types=1);

it('lists the files in their order', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'b.md', 'b']);
    $this->dfr([$id, 'write', 'a.md', 'a']);

    expect($this->dfr([$id, 'files'])->getOutput())->toBe("b.md\na.md\n");
});

it('puts the given files first and keeps the old order of the others', function () {
    $id = $this->init();

    foreach (['a.md', 'b.md', 'c.md', 'd.md'] as $fileName) {
        $this->dfr([$id, 'write', $fileName, 'text']);
    }

    $process = $this->dfr([$id, 'files:reorder', 'c.md', 'a.md']);

    expect($process->getExitCode())->toBe(0)
        ->and($this->dfr([$id, 'files'])->getOutput())->toBe("c.md\na.md\nb.md\nd.md\n");
});

it('fails to reorder and changes nothing when a file does not exist', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'a.md', 'a']);
    $this->dfr([$id, 'write', 'b.md', 'b']);
    $process = $this->dfr([$id, 'files:reorder', 'b.md', 'missing.md']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The thing has no file missing.md.')
        ->and($this->dfr([$id, 'files'])->getOutput())->toBe("a.md\nb.md\n");
});

it('moves archived files to .archive and removes them from the order', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'plan']);
    $this->dfr([$id, 'write', 'notes.md', 'notes']);
    $process = $this->dfr([$id, 'files:archive', 'plan.md']);

    expect($process->getExitCode())->toBe(0)
        ->and(file_get_contents($this->thingFolder($id).'/.archive/plan.md'))->toBe("plan\n")
        ->and($this->thingData($id)['files'])->toBe(['notes.md'])
        ->and($this->dfr([$id, 'files'])->getOutput())->toBe("notes.md\n")
        ->and($this->dfr([$id, 'read:all'])->getOutput())->toBe("==> notes.md <==\nnotes\n");
});

it('puts a number before the extension when the archive has the name', function () {
    $id = $this->init();

    foreach (['first', 'second', 'third'] as $content) {
        $this->dfr([$id, 'write', 'plan.md', $content]);
        $this->dfr([$id, 'files:archive', 'plan.md']);
    }

    $archive = $this->thingFolder($id).'/.archive';

    expect(file_get_contents("$archive/plan.md"))->toBe("first\n")
        ->and(file_get_contents("$archive/plan (2).md"))->toBe("second\n")
        ->and(file_get_contents("$archive/plan (3).md"))->toBe("third\n");
});

it('fails to archive and archives nothing when a name does not match a file exactly', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'plan']);
    $process = $this->dfr([$id, 'files:archive', 'plan.md', 'plan']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The thing has no file plan.')
        ->and(is_file($this->thingFolder($id).'/plan.md'))->toBeTrue();
});
