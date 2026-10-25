<?php

declare(strict_types=1);

it('shows all files in the order they were first written', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'todo.md', 'the todo']);
    $this->dfr([$id, 'write', 'plan.md', 'the plan']);
    $this->dfr([$id, 'write', 'todo.md', 'the new todo']);

    expect($this->dfr([$id, 'read:all'])->getOutput())
        ->toBe("==> todo.md <==\nthe new todo\n\n==> plan.md <==\nthe plan\n");
});

it('shows files made outside dfr last, and never dot files', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'the plan']);
    file_put_contents($this->thingFolder($id).'/notes.md', 'by hand');

    expect($this->dfr([$id, 'read:all'])->getOutput())
        ->toBe("==> plan.md <==\nthe plan\n\n==> notes.md <==\nby hand\n");
});

it('shows only the frontmatter with --fm and --frontmatter', function (string $option) {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\ndescription: The plan\n---\nthe plan\n");
    $this->dfr([$id, 'write', 'notes.md', 'no frontmatter']);

    expect($this->dfr([$id, 'read:all', $option])->getOutput())
        ->toBe("==> plan.md <==\ndescription: The plan\n\n==> notes.md <==\n");
})->with(['--fm', '--frontmatter']);

it('shows all files as JSON', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\nstatus: draft\n---\nBody\n");
    $this->dfr([$id, 'write', 'notes.md', 'notes']);

    expect(json_decode($this->dfr([$id, 'read:all', '--json'])->getOutput(), true))->toBe([
        ['file' => 'plan.md', 'frontmatter' => 'status: draft', 'body' => "Body\n"],
        ['file' => 'notes.md', 'frontmatter' => null, 'body' => "notes\n"],
    ]);
});

it('shows only the frontmatter as JSON with --fm', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\nstatus: draft\n---\nBody\n");

    expect(json_decode($this->dfr([$id, 'read:all', '--fm', '--json'])->getOutput(), true))
        ->toBe([['file' => 'plan.md', 'frontmatter' => 'status: draft']]);
});
