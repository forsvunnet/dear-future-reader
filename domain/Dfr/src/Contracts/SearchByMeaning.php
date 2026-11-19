<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

use Domain\Dfr\Data\MeaningMatch;

/**
 * Finds the context files whose meaning is close to the text, the best first.
 */
interface SearchByMeaning
{
    /**
     * @return list<MeaningMatch>
     */
    public function __invoke(string $text): array;
}
