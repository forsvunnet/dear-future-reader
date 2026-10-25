<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\ValidFileName;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommand extends Command
{
    protected $signature = 'dfr:read {id} {file}';

    protected $description = 'Show one file.';

    public function __construct(
        private ReadThing $readThing,
        private ValidFileName $validFileName,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $id = $this->argument('id');
        $fileName = $this->argument('file');
        $thing = ($this->readThing)($id);

        if ($thing === null) {
            $this->output->getErrorStyle()->writeln("No thing has the ID $id.");

            return 1;
        }

        if (! ($this->validFileName)($fileName) || ! is_file("$thing->folder/$fileName")) {
            $this->output->getErrorStyle()->writeln("The thing has no file $fileName.");

            return 1;
        }

        $this->output->write((string) file_get_contents("$thing->folder/$fileName"), false, OutputInterface::OUTPUT_RAW);

        return 0;
    }
}
