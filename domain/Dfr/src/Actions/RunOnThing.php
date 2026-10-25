<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Closure;
use Domain\Dfr\Data\LogEntry;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

/**
 * Runs the work of a command on a thing, and writes a log line so `dfr $ID log` shows which session ran it.
 * When no thing has the ID, it shows an error and runs nothing.
 */
class RunOnThing
{
    public function __construct(
        private ReadThing $readThing,
        private AppendLog $appendLog,
    ) {}

    /**
     * @param  Closure(Thing): int  $work  Returns the exit code.
     */
    public function __invoke(Command $command, string $id, Closure $work): int
    {
        $thing = ($this->readThing)($id);

        if ($thing === null) {
            $command->getOutput()->getErrorStyle()->writeln("No thing has the ID $id.");

            return 1;
        }

        $exitCode = $work($thing);
        $session = getenv('CLAUDE_CODE_SESSION_ID');

        ($this->appendLog)($thing, new LogEntry(
            time: gmdate('Y-m-d\TH:i:s\Z'),
            session: is_string($session) && $session !== '' ? $session : 'unknown',
            // "dfr:todo:tick" becomes "todo tick", the same words as `dfr $ID todo tick`.
            command: (string) preg_replace('/^todo:/', 'todo ', substr((string) $command->getName(), 4)),
            arguments: $this->arguments($command),
            exitCode: $exitCode,
        ));

        return $exitCode;
    }

    /**
     * The arguments after the ID, then the options that were given, as "--name=value".
     *
     * @return list<string>
     */
    public function arguments(Command $command): array
    {
        // The native definition has only the arguments and options of the command, not the global ones like --verbose.
        $definition = $command->getNativeDefinition();
        $arguments = [];

        foreach (array_keys($definition->getArguments()) as $name) {
            if ($name === 'id') {
                continue;
            }

            // An argument value is a string, or a list of strings for an argument like {files*}.
            array_push($arguments, ...array_filter((array) $command->argument($name), is_string(...)));
        }

        foreach (array_keys($definition->getOptions()) as $name) {
            $value = $command->option($name);

            if ($value === true) {
                $arguments[] = "--$name";
            }

            foreach (array_filter(is_array($value) ? $value : [$value], is_string(...)) as $item) {
                $arguments[] = "--$name=$item";
            }
        }

        return $arguments;
    }
}
