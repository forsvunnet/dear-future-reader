<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Returns the status text of the QMD index of the things. qmd makes an empty index when the index does not exist.
 */
class ReadQmdIndexStatus
{
    public function __construct(
        private RunQmd $runQmd,
    ) {}

    public function __invoke(): string
    {
        return ($this->runQmd)(['status']);
    }
}
