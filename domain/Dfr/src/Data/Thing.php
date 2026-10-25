<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class Thing
{
    /**
     * @param  list<string>  $files  The filenames in their order.
     */
    public function __construct(
        public string $id,
        public string $folder,
        public string $name,
        public array $files,
    ) {}
}
