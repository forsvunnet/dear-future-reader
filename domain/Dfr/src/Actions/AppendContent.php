<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Adds content to the end. Puts a line break between when the old content does not end with one.
 */
class AppendContent
{
    public function __invoke(string $oldContent, string $newContent): string
    {
        if ($oldContent === '' || str_ends_with($oldContent, "\n")) {
            return $oldContent.$newContent;
        }

        return "$oldContent\n$newContent";
    }
}
