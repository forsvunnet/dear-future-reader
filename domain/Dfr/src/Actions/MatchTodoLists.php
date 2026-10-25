<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\TodoList;

/**
 * Finds the todo lists whose title matches a name, with the same rule as MatchText.
 */
class MatchTodoLists
{
    public function __construct(
        private MatchText $matchText,
    ) {}

    /**
     * @param  list<TodoList>  $lists
     * @return list<TodoList>
     */
    public function __invoke(array $lists, string $name): array
    {
        $titled = array_filter($lists, fn (TodoList $list) => $list->title !== null);
        $keys = ($this->matchText)($name, array_map(fn (TodoList $list) => (string) $list->title, $titled));

        return array_map(fn (int $key) => $lists[$key], $keys);
    }
}
