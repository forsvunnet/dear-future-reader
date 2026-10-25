<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Adds a new todo list with a "##" title to the end of todo.md.
 */
class AddTodoList
{
    /**
     * @param  array<string>  $texts
     * @return string The new content of todo.md.
     */
    public function __invoke(Thing $thing, string $title, array $texts): string
    {
        $path = "$thing->folder/todo.md";
        $content = is_file($path) ? (string) file_get_contents($path) : '';
        $items = implode('', array_map(fn (string $text) => "- [ ] $text\n", $texts));

        if ($content === '') {
            return "## $title\n\n$items";
        }

        return rtrim($content, "\r\n")."\n\n## $title\n\n$items";
    }
}
