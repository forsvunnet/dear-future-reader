<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Contracts\FindGitRepository;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class ProjectAddCommand extends Command
{
    protected $signature = 'dfr:project:add {id} {path? : A folder in the git repository. The current folder when not given}';

    protected $description = 'Add the git repository of a folder to the projects of the thing.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
        private FindGitRepository $findGitRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $folder = $this->argument('path') ?? (string) getcwd();
            $repository = is_dir($folder) ? ($this->findGitRepository)($folder) : null;

            if ($repository === null) {
                $this->output->getErrorStyle()->writeln("$folder is not in a git repository.");

                return 1;
            }

            ($this->changeThing)($thing, function (Thing $current) use ($repository) {
                if (in_array($repository, $current->projects, true)) {
                    return null;
                }

                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    parent: $current->parent,
                    repository: $current->repository,
                    projects: [...$current->projects, $repository],
                    commits: $current->commits,
                    files: $current->files,
                );
            });

            return 0;
        });
    }
}
