<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\ThingsRoot;
use RuntimeException;

/**
 * Reads the location of the things from ~/.config/dfr/config.json. The default is ~/.dfr.
 */
class LoadThingsRoot
{
    public function __invoke(string $home): ThingsRoot
    {
        $configPath = "$home/.config/dfr/config.json";
        $path = '~/.dfr';

        if (is_file($configPath)) {
            $config = json_decode((string) file_get_contents($configPath), true, flags: JSON_THROW_ON_ERROR);
            $path = is_array($config) ? ($config['things_path'] ?? $path) : null;
        }

        if (! is_string($path) || $path === '') {
            throw new RuntimeException("things_path in $configPath must be a folder path.");
        }

        if (str_starts_with($path, '~/')) {
            $path = $home.substr($path, 1);
        }

        return new ThingsRoot(rtrim($path, '/'));
    }
}
