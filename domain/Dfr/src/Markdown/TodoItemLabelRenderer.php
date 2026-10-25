<?php

declare(strict_types=1);

namespace Domain\Dfr\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\Block\ParagraphRenderer;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Stringable;

/**
 * Renders the paragraph of a Markdown todo item with the checkbox and the text in one label.
 * The browser sends a click on the text to the checkbox.
 */
class TodoItemLabelRenderer implements NodeRendererInterface
{
    public function __construct(
        private ParagraphRenderer $paragraphRenderer,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        if (! $node instanceof Paragraph) {
            throw new InvalidArgumentException('The node must be a Paragraph.');
        }

        $html = $this->paragraphRenderer->render($node, $childRenderer);

        if (! $node->firstChild() instanceof TaskListItemMarker) {
            return $html;
        }

        // In a loose list the paragraph is a <p>. In a tight list it is the text only.
        if ($html instanceof HtmlElement) {
            $html->setContents(new HtmlElement('label', [], $html->getContents(false)));

            return $html;
        }

        return new HtmlElement('label', [], $html);
    }
}
