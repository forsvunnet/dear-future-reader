<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\GitCommit;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class GitRemoveCommand extends Command
{
    protected $signature = 'dfr:git:remove {id} {hash : The full hash or the first 4 or more characters of it}';

    protected $description = 'Remove the connection to a git commit.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $hash = strtolower($this->argument('hash'));

            // A short prefix like "a" could match a commit that was not meant. Git uses 4 characters at least too.
            if (preg_match('/^[0-9a-f]{4,}$/', $hash) !== 1) {
                $this->output->getErrorStyle()->writeln('Give the hash of the commit, with 4 or more characters.');

                return 1;
            }

            $error = null;

            ($this->changeThing)($thing, function (Thing $current) use ($hash, &$error) {
                $matching = array_filter($current->commits, fn (GitCommit $commit) => str_starts_with($commit->hash, $hash));
                $hashes = array_unique(array_map(fn (GitCommit $commit) => $commit->hash, $matching));

                if (count($hashes) !== 1) {
                    $error = $hashes === [] ? "The thing has no commit $hash." : "$hash matches more than one commit.";

                    return null;
                }

                // The same commit can be connected from more than one repository. Remove all of them.
                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    parent: $current->parent,
                    repository: $current->repository,
                    projects: $current->projects,
                    commits: array_values(array_diff_key($current->commits, $matching)),
                    files: $current->files,
                );
            });

            if ($error !== null) {
                $this->output->getErrorStyle()->writeln($error);

                return 1;
            }

            return 0;
        });
    }
}
