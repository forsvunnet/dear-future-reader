<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A title and the todo items under it.
 */
readonly class TodoList
{
    /**
     * @param  ?string  $title  Null when no text line is above the items.
     * @param  non-empty-list<TodoItem>  $items
     */
    public function __construct(
        public string $file,
        public ?string $title,
        public array $items,
    ) {}
}
