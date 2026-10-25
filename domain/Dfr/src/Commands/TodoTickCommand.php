<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeTodos;
use Domain\Dfr\Actions\PickTodoItems;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\TickTodoItems;
use Domain\Dfr\Data\TodoChange;
use Illuminate\Console\Command;

class TodoTickCommand extends Command
{
    protected $signature = 'dfr:todo:tick {id} {items*} {--list= : The name of the todo list. Without it, all lists are searched.}';

    protected $description = 'Tick todo items.';

    public function __construct(
        private ReadThing $readThing,
        private ChangeTodos $changeTodos,
        private PickTodoItems $pickTodoItems,
        private TickTodoItems $tickTodoItems,
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

        $name = $this->option('list');
        $searches = $this->argument('items');

        $errors = ($this->changeTodos)($thing, function (array $lists) use ($thing, $name, $searches) {
            $picked = ($this->pickTodoItems)($lists, $name, $searches);

            if ($picked->errors !== []) {
                return new TodoChange([], $picked->errors);
            }

            return new TodoChange(($this->tickTodoItems)($thing, $picked->items, true), []);
        });

        foreach ($errors as $error) {
            $this->output->getErrorStyle()->writeln($error);
        }

        return $errors === [] ? 0 : 1;
    }
}
