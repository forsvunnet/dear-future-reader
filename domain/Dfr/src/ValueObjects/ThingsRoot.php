<?php

declare(strict_types=1);

namespace Domain\Dfr\ValueObjects;

/**
 * The folder that holds the folders of all things.
 */
readonly class ThingsRoot
{
    public function __construct(
        public string $path,
    ) {}

    public function folder(string $id): string
    {
        return "$this->path/$id";
    }
}
