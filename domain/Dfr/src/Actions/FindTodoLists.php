<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoList;

/**
 * Finds the todo lists in all context files of a thing, in the order of the files.
 */
class FindTodoLists
{
    public function __construct(
        private OrderedFiles $orderedFiles,
        private ParseTodoLists $parseTodoLists,
    ) {}

    /**
     * @return list<TodoList>
     */
    public function __invoke(Thing $thing): array
    {
        $lists = [];

        foreach (($this->orderedFiles)($thing) as $fileName) {
            $content = (string) file_get_contents("$thing->folder/$fileName");
            $lists = [...$lists, ...($this->parseTodoLists)($fileName, $content)];
        }

        return $lists;
    }
}
