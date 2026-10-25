<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\CreateThing;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Contracts\FindGitRepository;
use Illuminate\Console\Command;

class InitCommand extends Command
{
    protected $signature = 'dfr:init {name : The name of the thing} {--parent= : The ID of the parent thing}';

    protected $description = 'Make a new thing and show its ID.';

    public function __construct(
        private CreateThing $createThing,
        private ReadThing $readThing,
        private FindGitRepository $findGitRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        $parent = $this->option('parent');

        if ($name === '') {
            $this->output->getErrorStyle()->writeln('Give the thing a name.');

            return 1;
        }

        if ($parent !== null && ($this->readThing)($parent) === null) {
            $this->output->getErrorStyle()->writeln("No thing has the ID $parent.");

            return 1;
        }

        $this->line(($this->createThing)($name, $parent, ($this->findGitRepository)((string) getcwd()))->id);

        return 0;
    }
}
