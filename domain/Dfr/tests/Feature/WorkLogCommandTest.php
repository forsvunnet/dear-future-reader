<?php

declare(strict_types=1);

it('starts work-log.md with a session header', function () {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'did', 'it'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);

    expect(file_get_contents($this->thingFolder($id).'/work-log.md'))
        ->toBe('## '.gmdate('Y-m-d')." session 1\n- did it\n");
});

it('adds no header for the same session', function () {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'first'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);
    $this->dfr([$id, 'work:log', 'second'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);

    expect(file_get_contents($this->thingFolder($id).'/work-log.md'))
        ->toBe('## '.gmdate('Y-m-d')." session 1\n- first\n- second\n");
});

it('adds the next session header for a new session', function () {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'first'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);
    $this->dfr([$id, 'work:log', 'second'], env: ['CLAUDE_CODE_SESSION_ID' => 's-2']);

    $date = gmdate('Y-m-d');
    expect(file_get_contents($this->thingFolder($id).'/work-log.md'))
        ->toBe("## $date session 1\n- first\n## $date session 2\n- second\n");
});

it('makes each piped line a list item', function () {
    $id = $this->init();
    $this->dfr([$id, 'work:log'], "Read the code.\n\n- Next: the tests.\n");

    expect(file_get_contents($this->thingFolder($id).'/work-log.md'))
        ->toBe('## '.gmdate('Y-m-d')." session 1\n- Read the code.\n- Next: the tests.\n");
});

it('fails with no text', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'work:log'], '');

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('Give the text')
        ->and(file_exists($this->thingFolder($id).'/work-log.md'))->toBeFalse();
});
