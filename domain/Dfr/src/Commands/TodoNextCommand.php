<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\FindTodoLists;
use Domain\Dfr\Actions\PickTodoList;
use Domain\Dfr\Actions\ReadThing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class TodoNextCommand extends Command
{
    protected $signature = 'dfr:todo:next {id} {--list= : The name of the todo list.}';

    protected $description = 'Show the first item that is not ticked.';

    public function __construct(
        private ReadThing $readThing,
        private FindTodoLists $findTodoLists,
        private PickTodoList $pickTodoList,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $id = $this->argument('id');
        $thing = ($this->readThing)($id);

        if ($thing === null) {
            $this->output->getErrorStyle()->writeln("No thing has the ID $id.");

            return 1;
        }

        $picked = ($this->pickTodoList)(($this->findTodoLists)($thing), $this->option('list'));

        if ($picked->list === null) {
            $this->output->getErrorStyle()->writeln((string) $picked->error);

            return 1;
        }

        foreach ($picked->list->items as $item) {
            if (! $item->ticked) {
                $this->output->writeln($item->text, OutputInterface::OUTPUT_RAW);

                return 0;
            }
        }

        $this->output->getErrorStyle()->writeln('All items in the list are ticked.');

        return 0;
    }
}
