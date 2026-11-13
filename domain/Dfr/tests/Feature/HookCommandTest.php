<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

/**
 * Runs a hook command with the hook JSON on stdin.
 *
 * @param  array<string, mixed>  $input
 */
function hook(object $test, string $hook, array $input, ?string $folder = null): Process
{
    return $test->dfr(["hook:$hook"], json_encode(['session_id' => 's-1', 'cwd' => $folder ?? $test->home, ...$input]));
}

/**
 * @return array<mixed> The content of the session file.
 */
function sessionData(object $test, string $session = 's-1'): array
{
    return (array) json_decode((string) file_get_contents("$test->home/.dfr/.sessions/$session.json"), true);
}

it('makes a thing on the first prompt of a session', function () {
    $repository = $this->makeRepository();
    $process = hook($this, 'prompt', ['prompt' => 'fix the login'], $repository);
    $id = sessionData($this)['thing'];

    expect($process->getExitCode())->toBe(0)
        ->and($this->thingData($id)['name'])->toBe('unnamed')
        ->and($this->thingData($id)['repository'])->toBe(realpath($repository))
        ->and($process->getOutput())->toContain("made the thing $id")
        ->and($process->getOutput())->toContain("this session works on the thing $id");
});

it('keeps the same thing on the next prompts', function () {
    hook($this, 'prompt', ['prompt' => 'one']);
    $id = sessionData($this)['thing'];
    $process = hook($this, 'prompt', ['prompt' => 'two']);

    expect(glob("$this->home/.dfr/*"))->toHaveCount(1)
        ->and($process->getOutput())->not->toContain('made the thing')
        ->and($process->getOutput())->toContain("this session works on the thing $id");
});

it('connects to the thing named at the start of the prompt', function (string $prompt) {
    $id = $this->init('login');
    $process = hook($this, 'prompt', ['prompt' => sprintf($prompt, strtoupper($id))]);

    expect(sessionData($this)['thing'])->toBe($id)
        ->and(glob("$this->home/.dfr/*"))->toHaveCount(1)
        ->and($process->getOutput())->toContain("this session works on the thing $id");
})->with(['dfr %s fix it', '/dfr %s', '  DFR %s']);

it('moves the session to a thing named in a later prompt', function () {
    hook($this, 'prompt', ['prompt' => 'one']);
    $id = $this->init('login');
    hook($this, 'prompt', ['prompt' => "dfr $id continue"]);

    expect(sessionData($this)['thing'])->toBe($id);
});

it('makes no thing when the named thing does not exist', function () {
    $process = hook($this, 'prompt', ['prompt' => 'dfr zzz go on']);

    expect($process->getOutput())->toContain('no thing has the ID zzz')
        ->and(glob("$this->home/.dfr/*"))->toBe([]);
});

it('does not see a word like list as an ID', function () {
    hook($this, 'prompt', ['prompt' => 'dfr list my things']);

    expect(glob("$this->home/.dfr/*"))->toHaveCount(1);
});

it('fails on a session ID that is not safe as a filename', function () {
    $process = $this->dfr(['hook:prompt'], json_encode(['session_id' => '../x', 'prompt' => 'hi']));

    expect($process->getExitCode())->toBe(1)
        ->and(is_dir("$this->home/.dfr"))->toBeFalse();
});

it('stores the time of an edit', function () {
    hook($this, 'prompt', ['prompt' => 'one']);
    hook($this, 'edit', []);

    expect(sessionData($this)['lastEdit'])->toMatch('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/');
});

it('blocks the stop after an edit with no change to the thing', function () {
    hook($this, 'prompt', ['prompt' => 'one']);
    hook($this, 'edit', []);
    $output = json_decode(hook($this, 'stop', ['stop_hook_active' => false])->getOutput(), true);

    expect($output['decision'])->toBe('block')
        ->and($output['reason'])->toContain(sessionData($this)['thing']);
});

it('lets the stop through', function (Closure $setUp) {
    hook($this, 'prompt', ['prompt' => 'one']);
    $setUp($this, sessionData($this)['thing']);
    $process = hook($this, 'stop', ['stop_hook_active' => false]);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('');
})->with([
    'with no edit' => fn () => function () {},
    'when the thing changed after the edit' => fn () => function (object $test, string $id) {
        hook($test, 'edit', []);
        $test->dfr([$id, 'write:append', 'work-log.md', 'did it'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);
    },
]);

it('blocks when only another session or a read changed the log', function () {
    hook($this, 'prompt', ['prompt' => 'one']);
    $id = sessionData($this)['thing'];
    hook($this, 'edit', []);
    $this->dfr([$id, 'write', 'work-log.md', 'x'], env: ['CLAUDE_CODE_SESSION_ID' => 'other']);
    $this->dfr([$id, 'read:all'], env: ['CLAUDE_CODE_SESSION_ID' => 's-1']);

    expect(hook($this, 'stop', [])->getOutput())->toContain('"decision":"block"');
});

it('does not block a second time', function () {
    hook($this, 'prompt', ['prompt' => 'one']);
    hook($this, 'edit', []);

    expect(hook($this, 'stop', ['stop_hook_active' => true])->getOutput())->toBe('');
});
