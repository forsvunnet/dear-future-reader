<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use RuntimeException;

/**
 * Moves a context file to the .archive folder of the thing.
 * When the name is taken there, a number goes before the extension: "plan (2).md".
 * Call it only while the thing is locked.
 */
class ArchiveFile
{
    public function __invoke(Thing $thing, string $fileName): void
    {
        $archive = "$thing->folder/.archive";

        if (! is_dir($archive) && ! mkdir($archive) && ! is_dir($archive)) {
            throw new RuntimeException("Could not make the folder $archive.");
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $base = $extension === '' ? $fileName : substr($fileName, 0, -strlen($extension) - 1);
        $suffix = $extension === '' ? '' : ".$extension";
        $target = "$archive/$fileName";

        for ($number = 2; file_exists($target); $number++) {
            $target = "$archive/$base ($number)$suffix";
        }

        if (! rename("$thing->folder/$fileName", $target)) {
            throw new RuntimeException("Could not move $fileName to $target.");
        }
    }
}
