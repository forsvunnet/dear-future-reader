<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Contracts\FindGitCommit;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class GitAddCommand extends Command
{
    protected $signature = 'dfr:git:add {id} {hash : The hash of a commit in the git repository of the current folder}';

    protected $description = 'Connect a git commit to the thing.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
        private FindGitCommit $findGitCommit,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $found = ($this->findGitCommit)((string) getcwd(), $this->argument('hash'));

            if ($found->commit === null) {
                $this->output->getErrorStyle()->writeln((string) $found->error);

                return 1;
            }

            $commit = $found->commit;

            ($this->changeThing)($thing, function (Thing $current) use ($commit) {
                if (in_array($commit, $current->commits)) {
                    return null;
                }

                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    parent: $current->parent,
                    repository: $current->repository,
                    projects: $current->projects,
                    commits: [...$current->commits, $commit],
                    files: $current->files,
                );
            });

            return 0;
        });
    }
}
