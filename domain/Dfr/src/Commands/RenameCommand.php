<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class RenameCommand extends Command
{
    protected $signature = 'dfr:rename {id} {name : The new name}';

    protected $description = 'Change the name of the thing. The ID stays the same.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $name = trim($this->argument('name'));

            if ($name === '') {
                $this->output->getErrorStyle()->writeln('Give the thing a name.');

                return 1;
            }

            ($this->changeThing)($thing, fn (Thing $current) => new Thing(
                id: $current->id,
                folder: $current->folder,
                name: $name,
                parent: $current->parent,
                commits: $current->commits,
                files: $current->files,
            ));

            return 0;
        });
    }
}
