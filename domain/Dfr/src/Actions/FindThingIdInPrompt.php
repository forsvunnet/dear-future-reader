<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Finds the thing ID in a prompt that starts with "dfr ID", like "dfr 3wz fix the login" or "/dfr 3wz".
 */
class FindThingIdInPrompt
{
    public function __invoke(string $prompt): ?string
    {
        // The characters are the ones MakeId uses, so "dfr list" or "dfr help" is not seen as an ID.
        if (preg_match('/^\s*\/?dfr\s+([2-9a-hjkmnp-z]{3,})\b/i', $prompt, $match) !== 1) {
            return null;
        }

        return strtolower($match[1]);
    }
}
