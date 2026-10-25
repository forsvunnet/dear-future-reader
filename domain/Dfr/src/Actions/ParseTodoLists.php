<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\TodoItem;
use Domain\Dfr\Data\TodoList;

/**
 * Finds the todo lists in one file.
 *
 * A list is a run of todo items. Blank lines between the items are part of the list.
 * The first line that is not blank and not an item ends the list.
 * The title is the nearest line above the first item that is not blank, without "#" marks and without a ":" at the end.
 * Lines in the frontmatter and in ``` or ~~~ code blocks are never items.
 */
class ParseTodoLists
{
    public function __construct(
        private BodyFirstLine $bodyFirstLine,
    ) {}

    /**
     * @return list<TodoList>
     */
    public function __invoke(string $fileName, string $content): array
    {
        $firstLine = ($this->bodyFirstLine)($content);

        $lists = [];
        $items = [];
        $title = null;
        $inCode = false;

        foreach (array_slice(explode("\n", $content), $firstLine, preserve_keys: true) as $index => $line) {
            $line = rtrim($line, "\r");
            $isFence = preg_match('/^\s*(```|~~~)/', $line) === 1;

            if (! $inCode && ! $isFence && preg_match('/^(\s*[-*+]\s+)\[([ xX])\](?:\s+(.*))?$/', $line, $match) === 1) {
                $items[] = new TodoItem($fileName, $index, $match[1], trim($match[3] ?? ''), $match[2] !== ' ');

                continue;
            }

            if (! $inCode && ! $isFence && trim($line) === '') {
                continue;
            }

            if ($items !== []) {
                $lists[] = new TodoList($fileName, $title, $items);
                $items = [];
            }

            $inCode = $isFence ? ! $inCode : $inCode;
            $title = $inCode || $isFence ? null : $this->title($line);
        }

        if ($items !== []) {
            $lists[] = new TodoList($fileName, $title, $items);
        }

        return $lists;
    }

    public function title(string $line): string
    {
        return trim(rtrim(trim(ltrim(trim($line), '#')), ':'));
    }
}
