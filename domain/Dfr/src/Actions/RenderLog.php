<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\ReadSessionTitle;
use Illuminate\Support\Carbon;

/**
 * Renders a .log file as plain text for the Web UI. The text is escaped, so it cannot run in the browser.
 * A time in UTC at the start of a line shows as a relative time, like "2 hours ago". The tooltip shows the full time.
 * A session line of work.log shows the current title of the session in place of the session ID. The tooltip shows the ID.
 * The page polls, so the relative time and the title stay current.
 */
class RenderLog
{
    public function __construct(
        private ReadSessionTitle $readSessionTitle,
    ) {}

    public function __invoke(string $content): string
    {
        $lines = explode("\n", e($content));

        foreach ($lines as $index => $line) {
            if (preg_match('/^(\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ)( .*)?$/', $line, $match) !== 1) {
                continue;
            }

            [, $time] = $match;
            $relative = Carbon::parse($time)->diffForHumans();
            $lines[$index] = "<time datetime=\"$time\" title=\"$time\" class=\"dfr-log-time\">$relative</time>".$this->session($match[2] ?? '');
        }

        return '<div class="dfr-log">'.implode("\n", $lines).'</div>';
    }

    /**
     * @param  string  $rest  The escaped text after the time, like " session 3 SESSION-ID".
     */
    public function session(string $rest): string
    {
        if (preg_match('/^( session \d+) ([A-Za-z0-9-]+)$/', $rest, $match) !== 1) {
            return $rest;
        }

        [, $session, $id] = $match;
        $title = e(($this->readSessionTitle)($id) ?? $id);

        return "$session <span title=\"$id\">$title</span>";
    }
}
