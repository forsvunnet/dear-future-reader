<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class YamlResult
{
    /**
     * @param  ?array<mixed>  $values  The parsed keys and values, or null when the YAML is not valid.
     */
    public function __construct(
        public ?array $values,
        public ?string $error,
    ) {}
}
