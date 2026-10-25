<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeTodos;
use Domain\Dfr\Actions\PickTodoItems;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Actions\TickTodoItems;
use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoChange;
use Illuminate\Console\Command;

class TodoUntickCommand extends Command
{
    protected $signature = 'dfr:todo:untick {id} {items*} {--list= : The name of the todo list. Without it, all lists are searched.}';

    protected $description = 'Untick todo items.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeTodos $changeTodos,
        private PickTodoItems $pickTodoItems,
        private TickTodoItems $tickTodoItems,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $name = $this->option('list');
            $searches = $this->argument('items');

            $errors = ($this->changeTodos)($thing, function (array $lists) use ($thing, $name, $searches) {
                $picked = ($this->pickTodoItems)($lists, $name, $searches);

                if ($picked->errors !== []) {
                    return new TodoChange([], $picked->errors);
                }

                return new TodoChange(($this->tickTodoItems)($thing, $picked->items, false), []);
            });

            foreach ($errors as $error) {
                $this->output->getErrorStyle()->writeln($error);
            }

            return $errors === [] ? 0 : 1;
        });
    }
}
