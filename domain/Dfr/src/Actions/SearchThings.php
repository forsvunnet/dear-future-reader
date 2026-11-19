<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\FindLines;
use Domain\Dfr\Data\FoundLine;
use Domain\Dfr\Data\SearchMatch;
use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\ThingSearchResult;

/**
 * Finds the things where each word of the text is in the context files, the name or the project names, with case ignored.
 * The words can be on different lines.
 *
 * The things where the name contains all the words come first. Then the things with the most matched lines come first.
 */
class SearchThings
{
    public function __construct(
        private ListThings $listThings,
        private OrderedFiles $orderedFiles,
        private ThingProjects $thingProjects,
        private FindLines $findLines,
    ) {}

    /**
     * @return list<ThingSearchResult>
     */
    public function __invoke(string $text): array
    {
        $words = array_values(array_unique(preg_split('/\s+/u', mb_strtolower(trim($text)), -1, PREG_SPLIT_NO_EMPTY) ?: []));

        if ($words === []) {
            return [];
        }

        $things = ($this->listThings)();
        $files = [];
        $paths = [];

        foreach ($things as $thing) {
            $files[$thing->id] = ($this->orderedFiles)($thing);

            foreach ($files[$thing->id] as $fileName) {
                $paths[] = "$thing->folder/$fileName";
            }
        }

        $linesByPath = [];

        foreach (($this->findLines)($paths, $words) as $line) {
            $linesByPath[$line->path][] = $line;
        }

        $results = [];
        $nameMatches = [];

        foreach ($things as $thing) {
            $matches = $this->matchesOf($thing, $files[$thing->id], $linesByPath);
            $name = mb_strtolower($thing->name);
            $labels = $name.' '.mb_strtolower(implode(' ', array_map(basename(...), ($this->thingProjects)($thing))));
            $haystack = $labels.' '.mb_strtolower(implode("\n", array_map(fn (SearchMatch $match) => $match->text, $matches)));

            if (! $this->containsAll($haystack, $words)) {
                continue;
            }

            $results[] = new ThingSearchResult($thing->id, $thing->name, $matches);
            $nameMatches[$thing->id] = $this->containsAll($name, $words);
        }

        // ponytail: two plain rules rank the results. For a better order, give each word a weight by how rare it is.
        usort($results, fn (ThingSearchResult $a, ThingSearchResult $b) => [$nameMatches[$b->id], count($b->matches)] <=> [$nameMatches[$a->id], count($a->matches)]);

        return $results;
    }

    /**
     * @param  list<string>  $fileNames  In their order.
     * @param  array<string, list<FoundLine>>  $linesByPath
     * @return list<SearchMatch>
     */
    private function matchesOf(Thing $thing, array $fileNames, array $linesByPath): array
    {
        $matches = [];

        foreach ($fileNames as $fileName) {
            foreach ($linesByPath["$thing->folder/$fileName"] ?? [] as $line) {
                $matches[] = new SearchMatch($fileName, $line->line, $line->text);
            }
        }

        return $matches;
    }

    /**
     * @param  list<string>  $words
     */
    private function containsAll(string $haystack, array $words): bool
    {
        foreach ($words as $word) {
            if (! str_contains($haystack, $word)) {
                return false;
            }
        }

        return true;
    }
}
