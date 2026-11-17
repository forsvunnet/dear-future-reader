<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Adds the lines of the text to work.log, each after the time, like "2026-11-14T12:00:00Z text".
 * A new session first gets the line "TIME session N SESSION-ID". The session ID is left out when it is not known.
 */
class AppendWorkLog
{
    public function __construct(
        private AppendContent $appendContent,
        private FindSessionLines $findSessionLines,
    ) {}

    /**
     * @param  ?string  $session  The session ID, or null when it is not known.
     */
    public function __invoke(string $oldContent, string $text, bool $newSession, ?string $session): string
    {
        $time = gmdate('Y-m-d\TH:i:s\Z');
        $lines = [];

        $sessionCount = count(($this->findSessionLines)($oldContent));

        // A file with no session line starts one, even when the session logged before, to another file.
        if ($newSession || $sessionCount === 0) {
            $lines[] = "$time session ".($sessionCount + 1).($session === null ? '' : " $session");
        }

        foreach (preg_split('/\R/', trim($text)) ?: [] as $line) {
            $line = trim($line);

            if ($line !== '') {
                $lines[] = "$time $line";
            }
        }

        return ($this->appendContent)($oldContent, implode("\n", $lines)."\n");
    }
}
