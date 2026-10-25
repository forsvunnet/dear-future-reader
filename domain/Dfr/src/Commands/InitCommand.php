<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\CreateThing;
use Illuminate\Console\Command;

class InitCommand extends Command
{
    protected $signature = 'dfr:init {name : The name of the thing}';

    protected $description = 'Make a new thing and show its ID.';

    public function __construct(
        private CreateThing $createThing,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = trim($this->argument('name'));

        if ($name === '') {
            $this->output->getErrorStyle()->writeln('Give the thing a name.');

            return 1;
        }

        $this->line(($this->createThing)($name)->id);

        return 0;
    }
}
