<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ListThings;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected $signature = 'dfr:list';

    protected $description = 'List all things with their IDs.';

    public function __construct(
        private ListThings $listThings,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        foreach (($this->listThings)() as $thing) {
            $this->output->writeln("$thing->id\t$thing->name", OutputInterface::OUTPUT_RAW);
        }

        return 0;
    }
}
