<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class ParentRemoveCommand extends Command
{
    protected $signature = 'dfr:parent:remove {id}';

    protected $description = 'Remove the parent of the thing.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            ($this->changeThing)($thing, fn (Thing $current) => new Thing(
                id: $current->id,
                folder: $current->folder,
                name: $current->name,
                parent: null,
                repository: $current->repository,
                projects: $current->projects,
                commits: $current->commits,
                files: $current->files,
            ));

            return 0;
        });
    }
}
