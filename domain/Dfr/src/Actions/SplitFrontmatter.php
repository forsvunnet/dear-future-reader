<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Document;

class SplitFrontmatter
{
    public function __invoke(string $content): Document
    {
        if (preg_match('/\A---\R(.*?)^---\h*$\R?/ms', $content, $match) !== 1) {
            return new Document(null, $content);
        }

        return new Document(rtrim($match[1], "\r\n"), substr($content, strlen($match[0])));
    }
}
