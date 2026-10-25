<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\TodoItem;
use Domain\Dfr\Data\TodoItemsResult;
use Domain\Dfr\Data\TodoList;

/**
 * Picks one todo item for each argument.
 * With a list name, it looks only in that list. Without one, it looks in all lists.
 */
class PickTodoItems
{
    public function __construct(
        private PickTodoList $pickTodoList,
        private MatchText $matchText,
    ) {}

    /**
     * @param  list<TodoList>  $lists
     * @param  array<string>  $arguments
     */
    public function __invoke(array $lists, ?string $listName, array $arguments): TodoItemsResult
    {
        if ($listName !== null) {
            $picked = ($this->pickTodoList)($lists, $listName);

            if ($picked->list === null) {
                return new TodoItemsResult([], [(string) $picked->error]);
            }

            $lists = [$picked->list];
        }

        $items = array_merge(...array_map(fn (TodoList $list) => $list->items, $lists));
        $texts = array_map(fn (TodoItem $item) => $item->text, $items);
        $found = [];
        $errors = [];

        foreach ($arguments as $argument) {
            $keys = ($this->matchText)($argument, $texts);

            if (count($keys) === 1) {
                $found[] = $items[$keys[0]];

                continue;
            }

            $errors[] = $keys === []
                ? "No todo item matches \"$argument\"."
                : "More than one todo item matches \"$argument\": ".implode(', ', array_map(fn (int $key) => "\"$texts[$key]\"", $keys)).'.';
        }

        return new TodoItemsResult($found, $errors);
    }
}
