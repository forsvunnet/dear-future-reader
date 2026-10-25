<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\TodoList;
use Domain\Dfr\Data\TodoListResult;

/**
 * Picks one todo list: the list that matches the name, or the only list in the thing when there is no name.
 */
class PickTodoList
{
    public function __construct(
        private MatchTodoLists $matchTodoLists,
    ) {}

    /**
     * @param  list<TodoList>  $lists
     */
    public function __invoke(array $lists, ?string $name): TodoListResult
    {
        $matches = $name === null ? $lists : ($this->matchTodoLists)($lists, $name);

        if (count($matches) === 1) {
            return new TodoListResult($matches[0], null);
        }

        if ($name === null) {
            return new TodoListResult(null, $lists === []
                ? 'The thing has no todo list.'
                : 'The thing has '.count($lists).' todo lists. Give one with --list.');
        }

        if ($matches === []) {
            return new TodoListResult(null, "No todo list matches \"$name\".");
        }

        $titles = implode(', ', array_map(fn (TodoList $list) => "\"$list->title\" in $list->file", $matches));

        return new TodoListResult(null, "More than one todo list matches \"$name\": $titles.");
    }
}
