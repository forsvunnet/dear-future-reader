<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Command\Command;

it('shows the session, the exit code and the command of each command on the thing', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'two words'], env: ['CLAUDE_CODE_SESSION_ID' => 'session-1']);
    $this->dfr([$id, 'todo', 'tick', 'missing', '--list', 'Setup'], env: ['CLAUDE_CODE_SESSION_ID' => 'session-2']);

    $lines = explode("\n", trim($this->dfr([$id, 'log'])->getOutput()));

    expect($lines)->toHaveCount(2)
        ->and($lines[0])->toMatch("/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ\tsession-1\t0\twrite plan.md 'two words'$/")
        ->and($lines[1])->toEndWith("\tsession-2\t1\ttodo tick missing --list=Setup");
});

it('uses the session unknown when the variable is not set', function () {
    $id = $this->init();
    $this->dfr([$id, 'status', 'done']);

    expect($this->dfr([$id, 'log'])->getOutput())->toContain("\tunknown\t0\tstatus done");
});

it('keeps each value of a list argument and each flag', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'a.md', 'a']);
    $this->dfr([$id, 'write', 'b.md', 'b']);
    $this->dfr([$id, 'files:reorder', 'b.md', 'a.md']);
    $this->dfr([$id, 'read:all', '--fm']);

    $entries = json_decode($this->dfr([$id, 'log', '--json'])->getOutput(), true);

    expect($entries[2])->toMatchArray(['command' => 'files:reorder', 'arguments' => ['b.md', 'a.md'], 'exitCode' => 0])
        ->and($entries[3])->toMatchArray(['command' => 'read:all', 'arguments' => ['--fm']]);
});

it('writes no log for an unknown ID', function () {
    $process = $this->dfr(['zzz', 'log']);

    expect($process->getExitCode())->toBe(1)
        ->and(is_dir("$this->home/.dfr/zzz"))->toBeFalse();
});

it('writes a log line for each command on a thing', function () {
    $id = $this->init();

    foreach (app(Kernel::class)->all() as $name => $command) {
        if (! str_starts_with($name, 'dfr:') || ! $command->getDefinition()->hasArgument('id')) {
            continue;
        }

        // The dfr script turns "todo tick" into dfr:todo:tick, and "parent:remove" into dfr:parent:remove.
        $words = str_starts_with($name, 'dfr:todo:') ? ['todo', substr($name, 9)] : [substr($name, 4)];

        // Each required argument gets a value, so the command runs. A command that fails is logged too.
        $arguments = array_map(fn () => 'x', array_filter(
            $command->getDefinition()->getArguments(),
            fn ($argument) => $argument->getName() !== 'id' && $argument->isRequired(),
        ));

        $this->dfr([$id, ...$words, ...array_values($arguments)]);
        $entries = json_decode($this->dfr([$id, 'log', '--json'])->getOutput(), true);

        expect(array_last($entries)['command'])->toBe(implode(' ', $words), "$name writes no log line.");
    }
});
