<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\ThingsRoot;

/**
 * Makes a random ID. IDs have 3 characters until all 3-character IDs are used, then 4, and so on.
 */
class MakeId
{
    public function __construct(
        private ThingsRoot $root,
    ) {}

    public function __invoke(): string
    {
        // Lowercase letters and digits, without the lookalikes 0, o, 1, l and i.
        $characters = '23456789abcdefghjkmnpqrstuvwxyz';
        $usedLengths = array_map(fn (string $folder) => strlen(basename($folder)), glob($this->root->path.'/*', GLOB_ONLYDIR) ?: []);

        // ponytail: random tries get slow when nearly all IDs of one length are used.
        // Pick from the list of free IDs instead if that ever matters.
        $id = '';
        $length = $this->length(strlen($characters), $usedLengths);

        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $id;
    }

    /**
     * @param  list<int>  $usedLengths  The length of each ID in use.
     */
    public function length(int $characterCount, array $usedLengths): int
    {
        $counts = array_count_values($usedLengths);
        $length = 3;

        while (($counts[$length] ?? 0) >= $characterCount ** $length) {
            $length++;
        }

        return $length;
    }
}
