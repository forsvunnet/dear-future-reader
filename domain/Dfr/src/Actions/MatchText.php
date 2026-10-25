<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Finds the texts that match a search text, with case ignored.
 * When some texts are equal to the search text, only those match.
 * Otherwise each text that contains the search text matches.
 */
class MatchText
{
    /**
     * @param  array<int, string>  $texts
     * @return list<int> The keys of the matching texts.
     */
    public function __invoke(string $search, array $texts): array
    {
        $search = mb_strtolower(trim($search));
        $lowered = array_map(mb_strtolower(...), $texts);

        $equal = array_keys($lowered, $search, true);

        if ($equal !== []) {
            return $equal;
        }

        return array_keys(array_filter($lowered, fn (string $text) => str_contains($text, $search)));
    }
}
