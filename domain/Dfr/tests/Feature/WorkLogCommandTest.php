<?php

declare(strict_types=1);

// A log line starts with the time, like "2026-11-14T12:00:00Z ".
$time = '\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ';

it('starts work.log with a session line', function () use ($time) {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'did', 'it'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);

    expect(file_get_contents($this->thingFolder($id).'/work.log'))
        ->toMatch("/\A$time session 1 s-1\n$time did it\n\z/");
});

it('adds no session line for the same session', function () use ($time) {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'first'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);
    $this->dfr([$id, 'work:log', 'second'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);

    expect(file_get_contents($this->thingFolder($id).'/work.log'))
        ->toMatch("/\A$time session 1 s-1\n$time first\n$time second\n\z/");
});

it('adds the next session line for a new session', function () use ($time) {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'first'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);
    $this->dfr([$id, 'work:log', 'second'], env: ['CLAUDE_CODE_SESSION_ID' => 's-2']);

    expect(file_get_contents($this->thingFolder($id).'/work.log'))
        ->toMatch("/\A$time session 1 s-1\n$time first\n$time session 2 s-2\n$time second\n\z/");
});

it('gives each piped line the time and skips blank lines', function () use ($time) {
    $id = $this->init();
    $this->dfr([$id, 'work:log'], "Read the code.\n\nNext: the tests.\n");

    expect(file_get_contents($this->thingFolder($id).'/work.log'))
        ->toMatch("/\A$time session 1\n$time Read the code.\n$time Next: the tests.\n\z/");
});

it('fails with no text', function () {
    $id = $this->init();
    $process = $this->dfr([$id, 'work:log'], '');

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('Give the text')
        ->and(file_exists($this->thingFolder($id).'/work.log'))->toBeFalse();
});

it('starts a session when work.log has no session line', function () use ($time) {
    $id = $this->init();
    $this->dfr([$id, 'work:log', 'first'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);
    unlink($this->thingFolder($id).'/work.log');
    $this->dfr([$id, 'work:log', 'second'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);

    expect(file_get_contents($this->thingFolder($id).'/work.log'))
        ->toMatch("/\A$time session 1 s-1\n$time second\n\z/");
});
