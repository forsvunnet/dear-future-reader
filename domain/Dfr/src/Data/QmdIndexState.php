<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * The state of the QMD index of the things, from the status text of qmd.
 */
readonly class QmdIndexState
{
    /**
     * @param  bool  $made  True when the index has the collection "things".
     * @param  int  $files  The number of files in the index.
     * @param  int  $pending  The number of files that wait for the embed.
     * @param  ?string  $updated  The time since the last update, as qmd writes it, like "12m ago". Null when qmd gives no time.
     */
    public function __construct(
        public bool $made,
        public int $files,
        public int $pending,
        public ?string $updated,
    ) {}
}
