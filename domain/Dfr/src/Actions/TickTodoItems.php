<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoItem;

/**
 * Ticks or unticks todo items.
 */
class TickTodoItems
{
    /**
     * @param  list<TodoItem>  $items
     * @return array<string, string> The new content for each file that has one of the items.
     */
    public function __invoke(Thing $thing, array $items, bool $ticked): array
    {
        $files = [];

        foreach ($items as $item) {
            $lines = $files[$item->file] ?? explode("\n", (string) file_get_contents("$thing->folder/$item->file"));
            // The line starts with the marker and then "[", so the box is the next character.
            $lines[$item->line] = substr_replace($lines[$item->line], $ticked ? 'x' : ' ', strlen($item->marker) + 1, 1);
            $files[$item->file] = $lines;
        }

        return array_map(fn (array $lines) => implode("\n", $lines), $files);
    }
}
