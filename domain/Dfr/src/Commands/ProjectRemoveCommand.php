<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class ProjectRemoveCommand extends Command
{
    protected $signature = 'dfr:project:remove {id} {path : The path of the project, as dfr list shows it}';

    protected $description = 'Remove a project from the thing.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $path = $this->argument('path');
            // The stored path has no symlinks, so resolve the given path the same way when it still exists.
            $resolved = realpath($path);
            $removed = false;

            ($this->changeThing)($thing, function (Thing $current) use ($path, $resolved, &$removed) {
                $projects = array_values(array_filter(
                    $current->projects,
                    fn (string $project) => $project !== $path && $project !== $resolved,
                ));

                if ($projects === $current->projects) {
                    return null;
                }

                $removed = true;

                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    parent: $current->parent,
                    repository: $current->repository,
                    projects: $projects,
                    commits: $current->commits,
                    files: $current->files,
                );
            });

            if (! $removed) {
                $this->output->getErrorStyle()->writeln("The thing has no project $path.");

                return 1;
            }

            return 0;
        });
    }
}
