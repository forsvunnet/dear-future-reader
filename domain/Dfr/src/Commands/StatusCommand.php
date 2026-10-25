<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{
    protected $signature = 'dfr:status {id} {status? : The new status. Free text. An empty text removes the status.}';

    protected $description = 'Show or set the status of the thing.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $status = $this->argument('status');

            if ($status === null) {
                if ($thing->status !== null) {
                    $this->output->writeln($thing->status, OutputInterface::OUTPUT_RAW);
                }

                return 0;
            }

            $status = trim($status);

            ($this->changeThing)($thing, fn (Thing $current) => new Thing(
                id: $current->id,
                folder: $current->folder,
                name: $current->name,
                status: $status === '' ? null : $status,
                parent: $current->parent,
                commits: $current->commits,
                files: $current->files,
            ));

            return 0;
        });
    }
}
