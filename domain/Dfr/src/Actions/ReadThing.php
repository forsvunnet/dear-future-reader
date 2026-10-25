<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\ValueObjects\ThingsRoot;
use RuntimeException;

/**
 * Reads a thing from the .thing.json file in its folder.
 */
class ReadThing
{
    public function __construct(
        private ThingsRoot $root,
    ) {}

    public function __invoke(string $id): ?Thing
    {
        // The ID becomes part of a path, so it must not contain "/" or "..".
        if (preg_match('/^[a-z0-9]+$/', $id) !== 1) {
            return null;
        }

        $folder = $this->root->folder($id);
        $dataPath = "$folder/.thing.json";

        if (! is_file($dataPath)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($dataPath), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data) || ! is_string($data['name'] ?? null) || ! is_array($data['files'] ?? null)) {
            throw new RuntimeException("$dataPath does not have a name and a list of files.");
        }

        return new Thing($id, $folder, $data['name'], array_values(array_filter($data['files'], is_string(...))));
    }
}
