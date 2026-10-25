<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoItem;

/**
 * Inserts new todo items at a line in the file of an item. The new items get the marker of that item.
 */
class InsertTodoItems
{
    /**
     * @param  int  $line  The index of the line that the first new item gets.
     * @param  array<string>  $texts
     * @return string The new content of the file.
     */
    public function __invoke(Thing $thing, TodoItem $item, int $line, array $texts): string
    {
        $lines = explode("\n", (string) file_get_contents("$thing->folder/$item->file"));
        // ponytail: new lines end with "\n" only, so a file with "\r\n" gets mixed line ends.
        array_splice($lines, $line, 0, array_map(fn (string $text) => "$item->marker[ ] $text", $texts));

        return implode("\n", $lines);
    }
}
