<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class FilesCommand extends Command
{
    protected $signature = 'dfr:files {id}';

    protected $description = 'List the files.';

    public function __construct(
        private RunOnThing $runOnThing,
        private OrderedFiles $orderedFiles,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            foreach (($this->orderedFiles)($thing) as $fileName) {
                $this->output->writeln($fileName, OutputInterface::OUTPUT_RAW);
            }

            return 0;
        });
    }
}
