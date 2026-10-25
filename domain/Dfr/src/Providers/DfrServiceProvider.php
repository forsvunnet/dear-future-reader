<?php

declare(strict_types=1);

namespace Domain\Dfr\Providers;

use Domain\Dfr\Actions\LoadThingsRoot;
use Domain\Dfr\Commands\FmAddCommand;
use Domain\Dfr\Commands\FmCommand;
use Domain\Dfr\Commands\InitCommand;
use Domain\Dfr\Commands\ListCommand;
use Domain\Dfr\Commands\ReadAllCommand;
use Domain\Dfr\Commands\ReadCommand;
use Domain\Dfr\Commands\TodoAddAfterCommand;
use Domain\Dfr\Commands\TodoAddBeforeCommand;
use Domain\Dfr\Commands\TodoAddCommand;
use Domain\Dfr\Commands\TodoNextCommand;
use Domain\Dfr\Commands\TodoTickCommand;
use Domain\Dfr\Commands\TodoUntickCommand;
use Domain\Dfr\Commands\WriteAppendCommand;
use Domain\Dfr\Commands\WriteCommand;
use Domain\Dfr\Commands\WritePrependCommand;
use Domain\Dfr\ValueObjects\ThingsRoot;
use Illuminate\Support\ServiceProvider;

class DfrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThingsRoot::class, fn () => (new LoadThingsRoot)((string) getenv('HOME')));
    }

    public function boot(): void
    {
        $this->commands([
            InitCommand::class,
            ListCommand::class,
            ReadCommand::class,
            ReadAllCommand::class,
            WriteCommand::class,
            WriteAppendCommand::class,
            WritePrependCommand::class,
            FmCommand::class,
            FmAddCommand::class,
            TodoAddCommand::class,
            TodoAddAfterCommand::class,
            TodoAddBeforeCommand::class,
            TodoTickCommand::class,
            TodoUntickCommand::class,
            TodoNextCommand::class,
        ]);
    }
}
