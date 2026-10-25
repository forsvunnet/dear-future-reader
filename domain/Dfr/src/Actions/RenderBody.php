<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\TodoItem;
use Domain\Dfr\Markdown\HighlightedCodeRenderer;
use Domain\Dfr\Markdown\TickableCheckboxRenderer;
use Domain\Dfr\Markdown\TodoItemLabelRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Renderer\Block\ParagraphRenderer;

/**
 * Renders the body of a context file from Markdown to HTML for the Web UI.
 * HTML in the file is escaped, so it cannot run in the browser.
 */
class RenderBody
{
    public function __construct(
        private SplitFrontmatter $splitFrontmatter,
        private BodyFirstLine $bodyFirstLine,
    ) {}

    /**
     * @param  list<TodoItem>  $items  The todo items of the file.
     */
    public function __invoke(string $fileName, string $content, array $items): string
    {
        $environment = new Environment(['html_input' => 'escape', 'allow_unsafe_links' => false]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        // The priority is higher than the default renderer of the checkbox, so this one wins.
        $environment->addRenderer(TaskListItemMarker::class, new TickableCheckboxRenderer(
            fileName: $fileName,
            hash: sha1($content),
            items: array_column(array_map(fn (TodoItem $item) => [$item->line, $item], $items), 1, 0),
            firstLine: ($this->bodyFirstLine)($content),
        ), 10);
        $environment->addRenderer(Paragraph::class, new TodoItemLabelRenderer(new ParagraphRenderer), 10);
        $environment->addRenderer(FencedCode::class, new HighlightedCodeRenderer(new FencedCodeRenderer), 10);

        return (string) (new MarkdownConverter($environment))->convert(($this->splitFrontmatter)($content)->body);
    }
}
