<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class FilesReorderCommand extends Command
{
    protected $signature = 'dfr:files:reorder {id} {files* : The filenames in the new order. The other files come after them.}';

    protected $description = 'Put the files in a new order.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
        private OrderedFiles $orderedFiles,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $given = array_values(array_unique($this->argument('files')));
            $missing = [];

            ($this->changeThing)($thing, function (Thing $current) use ($given, &$missing) {
                $files = ($this->orderedFiles)($current);
                $missing = array_diff($given, $files);

                if ($missing !== []) {
                    return null;
                }

                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    parent: $current->parent,
                    repository: $current->repository,
                    projects: $current->projects,
                    commits: $current->commits,
                    files: [...$given, ...array_diff($files, $given)],
                );
            });

            foreach ($missing as $fileName) {
                $this->output->getErrorStyle()->writeln("The thing has no file $fileName.");
            }

            return $missing === [] ? 0 : 1;
        });
    }
}
