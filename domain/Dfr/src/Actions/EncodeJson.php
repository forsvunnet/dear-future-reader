<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Encodes data as JSON that a person can read too.
 */
class EncodeJson
{
    public function __invoke(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
