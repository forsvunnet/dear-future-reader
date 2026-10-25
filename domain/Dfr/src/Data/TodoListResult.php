<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class TodoListResult
{
    public function __construct(
        public ?TodoList $list,
        public ?string $error,
    ) {}
}
