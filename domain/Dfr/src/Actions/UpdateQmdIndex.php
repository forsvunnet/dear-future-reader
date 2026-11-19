<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Indexes the new and changed files of the things again.
 */
class UpdateQmdIndex
{
    public function __construct(
        private RunQmd $runQmd,
    ) {}

    public function __invoke(): string
    {
        return ($this->runQmd)(['update']);
    }
}
