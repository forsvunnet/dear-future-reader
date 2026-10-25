<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Closure;
use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoChange;
use Domain\Dfr\Data\TodoList;

/**
 * Changes the todo lists of a thing while the thing is locked.
 * When the change has errors, no file is written.
 */
class ChangeTodos
{
    public function __construct(
        private LockThing $lockThing,
        private FindTodoLists $findTodoLists,
        private WriteFileAtomically $writeFile,
        private AddToOrder $addToOrder,
    ) {}

    /**
     * @param  Closure(list<TodoList>): TodoChange  $change
     * @return list<string> The errors.
     */
    public function __invoke(Thing $thing, Closure $change): array
    {
        return ($this->lockThing)($thing, function () use ($thing, $change) {
            $result = $change(($this->findTodoLists)($thing));

            if ($result->errors !== []) {
                return $result->errors;
            }

            foreach ($result->files as $fileName => $content) {
                ($this->writeFile)("$thing->folder/$fileName", $content);
                ($this->addToOrder)($thing, $fileName);
            }

            return [];
        });
    }
}
