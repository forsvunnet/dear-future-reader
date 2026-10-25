<?php

declare(strict_types=1);

it('replaces all of the frontmatter and keeps the body', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\nold: 1\n---\nthe plan\n");
    $this->dfr([$id, 'fm', 'plan.md', 'description: The plan']);

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("---\ndescription: The plan\n---\nthe plan\n");
});

it('adds frontmatter to a file without frontmatter', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'the plan']);
    $this->dfr([$id, 'fm', 'plan.md'], "description: The plan\nstatus: draft\n");

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))
        ->toBe("---\ndescription: The plan\nstatus: draft\n---\nthe plan\n");
});

it('fails on YAML that is not keys and values, and changes nothing', function (string $yaml) {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'the plan']);
    $process = $this->dfr([$id, 'fm', 'plan.md', $yaml]);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The YAML is not valid')
        ->and(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("the plan\n");
})->with(['a: [1', 'just text', '[1, 2]']);
