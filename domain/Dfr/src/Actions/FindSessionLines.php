<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Finds the lines in work.log that start a session, like "2026-11-14T12:00:00Z session 3 SESSION-ID". Old lines have no session ID.
 */
class FindSessionLines
{
    /**
     * @return list<int> The byte offset of each session line, the first one first.
     */
    public function __invoke(string $content): array
    {
        // ponytail: a logged text that is only "session 3" looks like a session line too.
        preg_match_all('/^\S+Z session \d+( [A-Za-z0-9-]+)?$/m', $content, $matches, PREG_OFFSET_CAPTURE);

        return array_column($matches[0], 1);
    }
}
