<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Actions\WouldMakeCircle;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class ParentCommand extends Command
{
    protected $signature = 'dfr:parent {id} {parent : The ID of the parent thing}';

    protected $description = 'Make the thing a child of another thing.';

    public function __construct(
        private ReadThing $readThing,
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
        private WouldMakeCircle $wouldMakeCircle,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $parentId = $this->argument('parent');
            if (($this->readThing)($parentId) === null) {
                $this->output->getErrorStyle()->writeln("No thing has the ID $parentId.");

                return 1;
            }

            $circle = false;

            // ponytail: the lock is on this thing only. Two sessions that make A the parent of B and B the parent of A
            // at the same moment can still make a circle. `list --tree` shows such things at the top.
            // Lock the whole things folder instead if that ever happens.
            ($this->changeThing)($thing, function (Thing $current) use ($parentId, &$circle) {
                if (($this->wouldMakeCircle)($current->id, $parentId)) {
                    $circle = true;

                    return null;
                }

                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    status: $current->status,
                    parent: $parentId,
                    commits: $current->commits,
                    files: $current->files,
                );
            });

            if ($circle) {
                $this->output->getErrorStyle()->writeln("$parentId is $thing->id or a child of $thing->id, so it cannot be the parent.");

                return 1;
            }

            return 0;
        });
    }
}
