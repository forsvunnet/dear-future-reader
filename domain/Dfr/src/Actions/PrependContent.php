<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Document;

/**
 * Adds content to the start of the body. The frontmatter stays first.
 */
class PrependContent
{
    public function __construct(
        private SplitFrontmatter $splitFrontmatter,
        private JoinFrontmatter $joinFrontmatter,
        private AppendContent $appendContent,
    ) {}

    public function __invoke(string $oldContent, string $newContent): string
    {
        $document = ($this->splitFrontmatter)($oldContent);
        $body = ($this->appendContent)($newContent, $document->body);

        return ($this->joinFrontmatter)(new Document($document->frontmatter, $body));
    }
}
