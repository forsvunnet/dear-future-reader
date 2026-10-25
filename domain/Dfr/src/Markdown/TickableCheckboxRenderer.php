<?php

declare(strict_types=1);

namespace Domain\Dfr\Markdown;

use Domain\Dfr\Data\TodoItem;
use InvalidArgumentException;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders the checkbox of a Markdown todo item as a checkbox that ticks or unticks the item.
 * The checkbox stays disabled when dfr does not see the line as a todo item, like "1. [ ] Step".
 */
class TickableCheckboxRenderer implements NodeRendererInterface
{
    /**
     * @param  string  $hash  The sha1 of the file. The tick fails when the file changed after the page loaded.
     * @param  array<int, TodoItem>  $items  The todo items of the file, by the index of their line.
     * @param  int  $firstLine  The index of the line in the file where the body starts.
     */
    public function __construct(
        private string $fileName,
        private string $hash,
        private array $items,
        private int $firstLine,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (! $node instanceof TaskListItemMarker) {
            throw new InvalidArgumentException('The node must be a TaskListItemMarker.');
        }

        $checkbox = new HtmlElement('input', ['type' => 'checkbox'], '', true);

        if ($node->isChecked()) {
            $checkbox->setAttribute('checked', '');
        }

        $item = $this->items[$this->line($node)] ?? null;

        if ($item === null) {
            $checkbox->setAttribute('disabled', '');

            return $checkbox;
        }

        $arguments = implode(', ', array_map(
            fn (string|int $value) => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            [$this->fileName, $item->line, $this->hash],
        ));

        // The click does not change the checkbox. The page shows the state of the file after the tick.
        $checkbox->setAttribute('wire:click.prevent', "tick($arguments)");
        // A new key after each change of the file makes Livewire put in a new checkbox with the correct state.
        $checkbox->setAttribute('wire:key', "$this->fileName:$item->line:$this->hash");

        return $checkbox;
    }

    /**
     * The index of the line in the file, or -1 when CommonMark does not know the line.
     */
    public function line(Node $node): int
    {
        $block = $node->parent();

        while ($block !== null && ! $block instanceof AbstractBlock) {
            $block = $block->parent();
        }

        $startLine = $block?->getStartLine();

        // CommonMark counts the lines of the body from 1.
        return $startLine === null ? -1 : $this->firstLine + $startLine - 1;
    }
}
