<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoChange;
use Domain\Dfr\Data\TodoItem;
use Domain\Dfr\Data\TodoItemsResult;
use Domain\Dfr\Data\TodoList;

/**
 * Ticks the todo item on a line when it is not ticked, and unticks it when it is ticked.
 * It changes nothing when the file changed after the caller read it.
 */
class ToggleTodoItem
{
    public function __construct(
        private ChangeTodos $changeTodos,
        private TickTodoItems $tickTodoItems,
    ) {}

    /**
     * @param  string  $fileName  A context file of the thing.
     * @param  string  $oldHash  The sha1 of the file when the caller read it.
     * @return TodoItemsResult The item as it was before the change, or the error.
     */
    public function __invoke(Thing $thing, string $fileName, int $line, string $oldHash): TodoItemsResult
    {
        $toggled = [];

        $errors = ($this->changeTodos)($thing, function (array $lists) use ($thing, $fileName, $line, $oldHash, &$toggled) {
            $path = "$thing->folder/$fileName";

            if (! is_file($path) || sha1((string) file_get_contents($path)) !== $oldHash) {
                return new TodoChange([], ["$fileName changed after the page loaded. The page now shows the new content."]);
            }

            $items = array_merge(...array_map(fn (TodoList $list) => $list->items, $lists));
            $toggled = array_values(array_filter($items, fn (TodoItem $item) => $item->file === $fileName && $item->line === $line));

            if ($toggled === []) {
                return new TodoChange([], ["$fileName has no todo item on line ".($line + 1).'.']);
            }

            return new TodoChange(($this->tickTodoItems)($thing, $toggled, ! $toggled[0]->ticked), []);
        });

        return new TodoItemsResult($errors === [] ? $toggled : [], $errors);
    }
}
