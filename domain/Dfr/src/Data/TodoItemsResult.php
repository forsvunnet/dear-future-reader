<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class TodoItemsResult
{
    /**
     * @param  list<TodoItem>  $items  One item for each argument, in the order of the arguments.
     * @param  list<string>  $errors  One error for each argument that matches no item or more than one item.
     */
    public function __construct(
        public array $items,
        public array $errors,
    ) {}
}
