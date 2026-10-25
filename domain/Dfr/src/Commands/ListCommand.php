<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\EncodeJson;
use Domain\Dfr\Actions\ListThings;
use Domain\Dfr\Actions\SortAsTree;
use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TreeRow;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected $signature = 'dfr:list
        {--status= : Show only the things with this exact status}
        {--tree : Show each child under its parent}
        {--json : Show the output as JSON}';

    protected $description = 'List all things with their IDs and status.';

    public function __construct(
        private ListThings $listThings,
        private SortAsTree $sortAsTree,
        private EncodeJson $encodeJson,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $status = $this->option('status');

        // A filtered list loses the parents of the things, and the JSON has the parent of each thing.
        if ($this->option('tree') && ($status !== null || $this->option('json'))) {
            $this->output->getErrorStyle()->writeln('--tree does not work together with --status or --json.');

            return 1;
        }

        $things = ($this->listThings)();

        if ($status !== null) {
            $things = array_values(array_filter($things, fn (Thing $thing) => $thing->status === $status));
        }

        if ($this->option('json')) {
            $rows = array_map(fn (Thing $thing) => [
                'id' => $thing->id,
                'name' => $thing->name,
                'status' => $thing->status,
                'parent' => $thing->parent,
            ], $things);

            $this->output->writeln(($this->encodeJson)($rows), OutputInterface::OUTPUT_RAW);

            return 0;
        }

        $rows = $this->option('tree') ? ($this->sortAsTree)($things) : array_map(fn (Thing $thing) => new TreeRow(0, $thing), $things);

        foreach ($rows as $row) {
            $indent = str_repeat('  ', $row->depth);
            $this->output->writeln("$indent{$row->thing->id}\t{$row->thing->name}\t{$row->thing->status}", OutputInterface::OUTPUT_RAW);
        }

        return 0;
    }
}
