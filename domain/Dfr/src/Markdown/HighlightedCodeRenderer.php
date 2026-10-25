<?php

declare(strict_types=1);

namespace Domain\Dfr\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Stringable;

/**
 * Renders a fenced code block with a language in a wrapper that Shiki colors in the browser.
 * See domain/Dfr/resources/js/highlight.js.
 */
class HighlightedCodeRenderer implements NodeRendererInterface
{
    public function __construct(
        private FencedCodeRenderer $fencedCodeRenderer,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('The node must be a FencedCode.');
        }

        $html = $this->fencedCodeRenderer->render($node, $childRenderer);
        $language = $node->getInfoWords()[0] ?? '';

        if ($language === '') {
            return $html;
        }

        // HtmlElement escapes the attribute values. The key changes with the code, so Livewire swaps the colored block.
        return new HtmlElement('div', [
            'x-data' => '',
            'x-highlight' => '',
            'data-lang' => $language,
            'wire:ignore' => '',
            'wire:key' => 'code-'.sha1($language."\n".$node->getLiteral()),
        ], $html);
    }
}
