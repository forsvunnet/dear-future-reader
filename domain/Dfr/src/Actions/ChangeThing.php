<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Closure;
use Domain\Dfr\Data\Thing;

/**
 * Changes the data of a thing while the thing is locked.
 */
class ChangeThing
{
    public function __construct(
        private LockThing $lockThing,
        private ReadThing $readThing,
        private SaveThing $saveThing,
    ) {}

    /**
     * @param  Closure(Thing): ?Thing  $change  Gets the thing as it is now and returns the changed thing, or null to change nothing.
     */
    public function __invoke(Thing $thing, Closure $change): void
    {
        ($this->lockThing)($thing, function () use ($thing, $change) {
            // Read the thing again, because another session can have changed it before the lock.
            $changed = $change(($this->readThing)($thing->id) ?? $thing);

            if ($changed !== null) {
                ($this->saveThing)($changed);
            }
        });
    }
}
