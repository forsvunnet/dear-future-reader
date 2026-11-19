<?php

declare(strict_types=1);

namespace Domain\Dfr\Providers;

use Domain\Dfr\Actions\FindGitCommitWithGit;
use Domain\Dfr\Actions\FindGitRepositoryWithGit;
use Domain\Dfr\Actions\FindLinesWithPhp;
use Domain\Dfr\Actions\FindLinesWithRipgrep;
use Domain\Dfr\Actions\LoadThingsRoot;
use Domain\Dfr\Actions\OpenFolderInFinder;
use Domain\Dfr\Actions\ReadGitCommitChangesWithGit;
use Domain\Dfr\Actions\ReadGitCommitHeaderWithGit;
use Domain\Dfr\Actions\ReadSessionTitleFromClaudeCode;
use Domain\Dfr\Commands\FilesArchiveCommand;
use Domain\Dfr\Commands\FilesCommand;
use Domain\Dfr\Commands\FilesReorderCommand;
use Domain\Dfr\Commands\FmAddCommand;
use Domain\Dfr\Commands\FmCommand;
use Domain\Dfr\Commands\GitAddCommand;
use Domain\Dfr\Commands\GitRemoveCommand;
use Domain\Dfr\Commands\HookEditCommand;
use Domain\Dfr\Commands\HookPromptCommand;
use Domain\Dfr\Commands\HookStopCommand;
use Domain\Dfr\Commands\InitCommand;
use Domain\Dfr\Commands\ListCommand;
use Domain\Dfr\Commands\LogCommand;
use Domain\Dfr\Commands\ParentCommand;
use Domain\Dfr\Commands\ParentRemoveCommand;
use Domain\Dfr\Commands\ProjectAddCommand;
use Domain\Dfr\Commands\ProjectRemoveCommand;
use Domain\Dfr\Commands\ReadAllCommand;
use Domain\Dfr\Commands\ReadCommand;
use Domain\Dfr\Commands\RenameCommand;
use Domain\Dfr\Commands\SearchCommand;
use Domain\Dfr\Commands\TodoAddAfterCommand;
use Domain\Dfr\Commands\TodoAddBeforeCommand;
use Domain\Dfr\Commands\TodoAddCommand;
use Domain\Dfr\Commands\TodoNextCommand;
use Domain\Dfr\Commands\TodoTickCommand;
use Domain\Dfr\Commands\TodoUntickCommand;
use Domain\Dfr\Commands\WriteAppendCommand;
use Domain\Dfr\Commands\WriteCommand;
use Domain\Dfr\Commands\WorkLogCommand;
use Domain\Dfr\Commands\WritePrependCommand;
use Domain\Dfr\Contracts\FindGitCommit;
use Domain\Dfr\Contracts\FindGitRepository;
use Domain\Dfr\Contracts\FindLines;
use Domain\Dfr\Contracts\OpenFolder;
use Domain\Dfr\Contracts\ReadGitCommitChanges;
use Domain\Dfr\Contracts\ReadGitCommitHeader;
use Domain\Dfr\Contracts\ReadSessionTitle;
use Domain\Dfr\ValueObjects\ThingsRoot;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Process\ExecutableFinder;

class DfrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThingsRoot::class, fn () => (new LoadThingsRoot)((string) getenv('HOME')));
        $this->app->bind(FindGitCommit::class, FindGitCommitWithGit::class);
        $this->app->bind(FindGitRepository::class, FindGitRepositoryWithGit::class);
        $this->app->bind(ReadGitCommitHeader::class, ReadGitCommitHeaderWithGit::class);
        $this->app->bind(ReadGitCommitChanges::class, ReadGitCommitChangesWithGit::class);
        $this->app->bind(OpenFolder::class, OpenFolderInFinder::class);
        // A web server like Herd runs PHP with a short PATH, so also look in the folders where Homebrew installs rg.
        $this->app->bind(FindLines::class, function () {
            $rg = (new ExecutableFinder)->find('rg', null, ['/opt/homebrew/bin', '/usr/local/bin']);

            return $rg === null ? new FindLinesWithPhp : new FindLinesWithRipgrep($rg);
        });
        // Claude Code keeps its files in CLAUDE_CONFIG_DIR, or in ~/.claude when it is not set.
        $this->app->bind(ReadSessionTitle::class, fn () => new ReadSessionTitleFromClaudeCode(
            (string) (getenv('CLAUDE_CONFIG_DIR') ?: getenv('HOME').'/.claude'),
        ));

        // `php artisan serve` passes only a few env vars to the PHP server. The Web UI needs HOME to find the things, and both to find the Claude Code sessions.
        ServeCommand::$passthroughVariables[] = 'HOME';
        ServeCommand::$passthroughVariables[] = 'CLAUDE_CONFIG_DIR';
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
            WorkLogCommand::class,
            FmCommand::class,
            FmAddCommand::class,
            TodoAddCommand::class,
            TodoAddAfterCommand::class,
            TodoAddBeforeCommand::class,
            TodoTickCommand::class,
            TodoUntickCommand::class,
            TodoNextCommand::class,
            ParentCommand::class,
            ParentRemoveCommand::class,
            RenameCommand::class,
            SearchCommand::class,
            LogCommand::class,
            GitAddCommand::class,
            GitRemoveCommand::class,
            ProjectAddCommand::class,
            ProjectRemoveCommand::class,
            FilesCommand::class,
            FilesReorderCommand::class,
            FilesArchiveCommand::class,
            HookPromptCommand::class,
            HookEditCommand::class,
            HookStopCommand::class,
        ]);

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'dfr');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
    }
}
