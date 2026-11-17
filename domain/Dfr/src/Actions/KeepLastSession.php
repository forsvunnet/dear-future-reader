<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Cuts work.log to the last session, the part from the last line "TIME session N". A note at the top says how many sessions it left out.
 */
class KeepLastSession
{
    public function __construct(
        private FindSessionLines $findSessionLines,
    ) {}

    public function __invoke(string $content, string $id): string
    {
        $offsets = ($this->findSessionLines)($content);

        if (count($offsets) < 2) {
            return $content;
        }

        $start = $offsets[array_key_last($offsets)];
        $leftOut = count($offsets) - 1;
        $sessions = $leftOut === 1 ? 'session' : 'sessions';

        return "($leftOut older $sessions left out. Run `dfr $id read work.log` to see all.)\n".substr($content, $start);
    }
}
