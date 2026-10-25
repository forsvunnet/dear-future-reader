<?php

declare(strict_types=1);

namespace Domain\Dfr\Providers;

use Domain\Dfr\Actions\FindGitCommitWithGit;
use Domain\Dfr\Actions\LoadThingsRoot;
use Domain\Dfr\Commands\FilesArchiveCommand;
use Domain\Dfr\Commands\FilesCommand;
use Domain\Dfr\Commands\FilesReorderCommand;
use Domain\Dfr\Commands\FmAddCommand;
use Domain\Dfr\Commands\FmCommand;
use Domain\Dfr\Commands\GitAddCommand;
use Domain\Dfr\Commands\GitRemoveCommand;
use Domain\Dfr\Commands\InitCommand;
use Domain\Dfr\Commands\ListCommand;
use Domain\Dfr\Commands\LogCommand;
use Domain\Dfr\Commands\ParentCommand;
use Domain\Dfr\Commands\ParentRemoveCommand;
use Domain\Dfr\Commands\ReadAllCommand;
use Domain\Dfr\Commands\ReadCommand;
use Domain\Dfr\Commands\RenameCommand;
use Domain\Dfr\Commands\SearchCommand;
use Domain\Dfr\Commands\StatusCommand;
use Domain\Dfr\Commands\TodoAddAfterCommand;
use Domain\Dfr\Commands\TodoAddBeforeCommand;
use Domain\Dfr\Commands\TodoAddCommand;
use Domain\Dfr\Commands\TodoNextCommand;
use Domain\Dfr\Commands\TodoTickCommand;
use Domain\Dfr\Commands\TodoUntickCommand;
use Domain\Dfr\Commands\WriteAppendCommand;
use Domain\Dfr\Commands\WriteCommand;
use Domain\Dfr\Commands\WritePrependCommand;
use Domain\Dfr\Contracts\FindGitCommit;
use Domain\Dfr\ValueObjects\ThingsRoot;
use Illuminate\Support\ServiceProvider;

class DfrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThingsRoot::class, fn () => (new LoadThingsRoot)((string) getenv('HOME')));
        $this->app->bind(FindGitCommit::class, FindGitCommitWithGit::class);
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
            StatusCommand::class,
            ParentCommand::class,
            ParentRemoveCommand::class,
            RenameCommand::class,
            SearchCommand::class,
            LogCommand::class,
            GitAddCommand::class,
            GitRemoveCommand::class,
            FilesCommand::class,
            FilesReorderCommand::class,
            FilesArchiveCommand::class,
        ]);

    }
}
