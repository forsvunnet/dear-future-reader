<?php

declare(strict_types=1);

it('adds and replaces attributes and keeps the others', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\ndescription: Old\nowner: me\n---\nthe plan\n");
    $this->dfr([$id, 'fm:add', 'plan.md'], "description: New\nstatus: draft\n");

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))
        ->toBe("---\ndescription: New\nowner: me\nstatus: draft\n---\nthe plan\n");
});

it('fails on invalid frontmatter in the file, and changes nothing', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\na: [1\n---\nthe plan\n");
    $process = $this->dfr([$id, 'fm:add', 'plan.md', 'b: 2']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The frontmatter in plan.md is not valid')
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("---\na: [1\n---\nthe plan\n");
});
