<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A context file, split into its frontmatter and its body.
 */
readonly class Document
{
    /**
     * @param  ?string  $frontmatter  The YAML between the "---" lines, or null when the file has no frontmatter.
     */
    public function __construct(
        public ?string $frontmatter,
        public string $body,
    ) {}
}
