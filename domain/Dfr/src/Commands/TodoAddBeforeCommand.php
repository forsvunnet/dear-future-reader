<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeTodos;
use Domain\Dfr\Actions\InsertTodoItems;
use Domain\Dfr\Actions\PickTodoItems;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Data\TodoChange;
use Illuminate\Console\Command;

class TodoAddBeforeCommand extends Command
{
    protected $signature = 'dfr:todo:add:before {id} {item : The item to add before.} {items*} {--list= : The name of the todo list. Without it, all lists are searched.}';

    protected $description = 'Add items before an item.';

    public function __construct(
        private ReadThing $readThing,
        private ChangeTodos $changeTodos,
        private PickTodoItems $pickTodoItems,
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
        $search = $this->argument('item');
        $texts = $this->argument('items');

        $errors = ($this->changeTodos)($thing, function (array $lists) use ($thing, $name, $search, $texts) {
            $picked = ($this->pickTodoItems)($lists, $name, [$search]);

            if ($picked->errors !== []) {
                return new TodoChange([], $picked->errors);
            }

            $item = $picked->items[0];

            return new TodoChange([$item->file => ($this->insertTodoItems)($thing, $item, $item->line, $texts)], []);
        });

        foreach ($errors as $error) {
            $this->output->getErrorStyle()->writeln($error);
        }

        return $errors === [] ? 0 : 1;
    }
}
