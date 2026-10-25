<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A thing in a tree, with the number of parents above it.
 */
readonly class TreeRow
{
    public function __construct(
        public int $depth,
        public Thing $thing,
    ) {}
}
