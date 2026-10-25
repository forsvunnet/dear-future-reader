<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Document;

class JoinFrontmatter
{
    public function __invoke(Document $document): string
    {
        if ($document->frontmatter === null || trim($document->frontmatter) === '') {
            return $document->body;
        }

        return "---\n$document->frontmatter\n---\n$document->body";
    }
}
