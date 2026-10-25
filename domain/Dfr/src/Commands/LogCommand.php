<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\EncodeJson;
use Domain\Dfr\Actions\ReadLog;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class LogCommand extends Command
{
    protected $signature = 'dfr:log {id} {--json : Show the output as JSON}';

    protected $description = 'Show which session ran which command on the thing, and when.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ReadLog $readLog,
        private EncodeJson $encodeJson,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $entries = ($this->readLog)($thing);

            if ($this->option('json')) {
                $this->output->writeln(($this->encodeJson)($entries), OutputInterface::OUTPUT_RAW);

                return 0;
            }

            foreach ($entries as $entry) {
                // Quote the arguments like a shell does, so "tick 'a b'" and "tick a b" look different.
                // ponytail: an argument with a line break still breaks the line. --json shows it exactly.
                $arguments = array_map(
                    fn (string $argument) => preg_match('/^[\w.,:\/=@%+-]+$/', $argument) === 1 ? $argument : escapeshellarg($argument),
                    $entry->arguments,
                );
                $command = implode(' ', [$entry->command, ...$arguments]);

                $this->output->writeln("$entry->time\t$entry->session\t$entry->exitCode\t$command", OutputInterface::OUTPUT_RAW);
            }

            return 0;
        });
    }
}
