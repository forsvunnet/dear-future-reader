<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Finds the index of the line in a file where the body starts, after the frontmatter.
 */
class BodyFirstLine
{
    public function __construct(
        private SplitFrontmatter $splitFrontmatter,
    ) {}

    public function __invoke(string $content): int
    {
        $body = ($this->splitFrontmatter)($content)->body;

        return substr_count(substr($content, 0, strlen($content) - strlen($body)), "\n");
    }
}
