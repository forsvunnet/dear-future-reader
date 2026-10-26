<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ArchiveFile;
use Domain\Dfr\Actions\ChangeThing;
use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class FilesArchiveCommand extends Command
{
    protected $signature = 'dfr:files:archive {id} {files* : The filenames to archive}';

    protected $description = 'Archive the given files.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ChangeThing $changeThing,
        private OrderedFiles $orderedFiles,
        private ArchiveFile $archiveFile,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $given = array_values(array_unique($this->argument('files')));
            $missing = [];

            ($this->changeThing)($thing, function (Thing $current) use ($given, &$missing) {
                $missing = array_diff($given, ($this->orderedFiles)($current));

                if ($missing !== []) {
                    return null;
                }

                foreach ($given as $fileName) {
                    ($this->archiveFile)($current, $fileName);
                }

                return new Thing(
                    id: $current->id,
                    folder: $current->folder,
                    name: $current->name,
                    parent: $current->parent,
                    repository: $current->repository,
                    projects: $current->projects,
                    commits: $current->commits,
                    files: array_values(array_diff($current->files, $given)),
                );
            });

            foreach ($missing as $fileName) {
                $this->output->getErrorStyle()->writeln("The thing has no file $fileName.");
            }

            return $missing === [] ? 0 : 1;
        });
    }
}
