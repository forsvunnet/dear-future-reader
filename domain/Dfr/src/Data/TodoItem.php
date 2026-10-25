<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * One Markdown todo item, like "- [x] Install packages".
 */
readonly class TodoItem
{
    /**
     * @param  int  $line  The index of the line in the file, from 0.
     * @param  string  $marker  The text before "[", like "  - ". A new item next to this one gets the same marker.
     */
    public function __construct(
        public string $file,
        public int $line,
        public string $marker,
        public string $text,
        public bool $ticked,
    ) {}
}
