<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Adds the lines of the text to work-log.md as list items. A new session first gets the header "## DATE session N".
 */
class AppendWorkLog
{
    public function __construct(
        private AppendContent $appendContent,
    ) {}

    public function __invoke(string $oldContent, string $text, bool $newSession): string
    {
        $lines = [];

        if ($newSession) {
            $sessionNumber = preg_match_all('/^## /m', $oldContent) + 1;
            $lines[] = '## '.gmdate('Y-m-d')." session $sessionNumber";
        }

        foreach (preg_split('/\R/', trim($text)) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $lines[] = str_starts_with($line, '- ') ? $line : "- $line";
        }

        return ($this->appendContent)($oldContent, implode("\n", $lines)."\n");
    }
}
