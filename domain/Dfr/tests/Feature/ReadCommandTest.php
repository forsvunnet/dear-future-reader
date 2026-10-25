<?php

declare(strict_types=1);

it('shows the file exactly as it is', function () {
    $id = $this->init();
    file_put_contents($this->thingFolder($id).'/plan.md', "<info>tags</info> stay\n");

    expect($this->dfr([$id, 'read', 'plan.md'])->getOutput())->toBe("<info>tags</info> stay\n");
});

it('fails on a missing file', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'read', 'plan.md']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('The thing has no file plan.md.');
});

it('does not read files outside the thing', function () {
    $id = $this->init();

    expect($this->dfr([$id, 'read', '../../.bashrc'])->getExitCode())->toBe(1)
        ->and($this->dfr([$id, 'read', '.thing.json'])->getExitCode())->toBe(1);
});

it('fails on an unknown ID', function (array $command) {
    $process = $this->dfr($command);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('No thing has the ID');
})->with([
    'read' => [['zzz', 'read', 'plan.md']],
    'read:all' => [['zzz', 'read:all']],
    'write' => [['zzz', 'write', 'plan.md', 'text']],
    'write:append' => [['zzz', 'write:append', 'plan.md', 'text']],
    'write:prepend' => [['zzz', 'write:prepend', 'plan.md', 'text']],
    'fm' => [['zzz', 'fm', 'plan.md', 'a: 1']],
    'fm:add' => [['zzz', 'fm:add', 'plan.md', 'a: 1']],
    'a path as ID' => [['../x', 'read:all']],
]);

it('shows the file as JSON, with the frontmatter and the body apart', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\nstatus: draft\n---\nBody\n");

    expect(json_decode($this->dfr([$id, 'read', 'plan.md', '--json'])->getOutput(), true))
        ->toBe(['file' => 'plan.md', 'frontmatter' => 'status: draft', 'body' => "Body\n"]);
});
