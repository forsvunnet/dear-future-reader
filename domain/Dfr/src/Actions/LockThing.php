<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Closure;
use Domain\Dfr\Data\Thing;
use Illuminate\Filesystem\LockableFile;

/**
 * Runs a change while the thing is locked.
 * The lock stops two sessions from losing each other's changes.
 */
class LockThing
{
    /**
     * @template T
     *
     * @param  Closure(): T  $change
     * @return T
     */
    public function __invoke(Thing $thing, Closure $change): mixed
    {
        $lock = new LockableFile("$thing->folder/.lock", 'c+');
        $lock->getExclusiveLock(true);

        try {
            return $change();
        } finally {
            $lock->close();
        }
    }
}
