<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\AddTodoList;
use Domain\Dfr\Actions\ChangeTodos;
use Domain\Dfr\Actions\InsertTodoItems;
use Domain\Dfr\Actions\MatchTodoLists;
use Domain\Dfr\Actions\PickTodoList;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Data\TodoChange;
use Domain\Dfr\Data\TodoList;
use Illuminate\Console\Command;

class TodoAddCommand extends Command
{
    protected $signature = 'dfr:todo:add {id} {items*} {--list= : The name of the todo list. A new list goes in todo.md.}';

    protected $description = 'Add items to the end of a todo list.';

    public function __construct(
        private ReadThing $readThing,
        private ChangeTodos $changeTodos,
        private MatchTodoLists $matchTodoLists,
        private PickTodoList $pickTodoList,
        private AddTodoList $addTodoList,
        private InsertTodoItems $insertTodoItems,
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
        $texts = $this->argument('items');

        $errors = ($this->changeTodos)($thing, function (array $lists) use ($thing, $name, $texts) {
            if ($name !== null && ($this->matchTodoLists)($lists, $name) === []) {
                return new TodoChange(['todo.md' => ($this->addTodoList)($thing, $name, $texts)], []);
            }

            $picked = ($this->pickTodoList)($lists, $name);

            if ($picked->list === null) {
                return new TodoChange([], [(string) $picked->error]);
            }

            $last = $picked->list->items[array_key_last($picked->list->items)];

            return new TodoChange([$last->file => ($this->insertTodoItems)($thing, $last, $last->line + 1, $texts)], []);
        });

        foreach ($errors as $error) {
            $this->output->getErrorStyle()->writeln($error);
        }

        return $errors === [] ? 0 : 1;
    }
}
