<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Cuts work-log.md to the last session, the part from the last "## " header. A note at the top says how many sessions it left out.
 */
class KeepLastSession
{
    public function __invoke(string $content, string $id): string
    {
        $count = preg_match_all('/^## /m', $content, $matches, PREG_OFFSET_CAPTURE);

        if ($count < 2) {
            return $content;
        }

        $start = $matches[0][$count - 1][1];
        $leftOut = $count - 1;
        $sessions = $leftOut === 1 ? 'session' : 'sessions';

        return "($leftOut older $sessions left out. Run `dfr $id read work-log.md` to see all.)\n".substr($content, $start);
    }
}
