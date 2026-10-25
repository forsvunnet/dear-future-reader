<?php

declare(strict_types=1);

namespace Domain\Dfr\Tests;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Runs the real dfr script with a temporary home folder, so the tests never touch ~/.dfr.
 */
abstract class DfrTestCase extends TestCase
{
    public string $home;

    protected function setUp(): void
    {
        parent::setUp();

        $this->home = sys_get_temp_dir().'/dfr-test-'.bin2hex(random_bytes(4));
        mkdir($this->home);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->home);

        parent::tearDown();
    }

    /**
     * @param  list<string>  $arguments
     * @param  ?string  $folder  The current folder of the command. The default is the home folder.
     * @param  array<string, string>  $env
     */
    public function dfr(array $arguments, ?string $input = null, ?string $folder = null, array $env = []): Process
    {
        // false removes the variable, so a test never gets the session of the Claude Code that runs it.
        $env = [...['HOME' => $this->home, 'CLAUDE_CODE_SESSION_ID' => false], ...$env];
        $process = new Process([base_path('dfr'), ...$arguments], $folder ?? $this->home, $env, $input);
        $process->run();

        return $process;
    }

    /**
     * Makes a thing and returns its ID.
     */
    public function init(string $name = 'test'): string
    {
        return trim($this->dfr(['init', $name])->getOutput());
    }

    public function thingFolder(string $id): string
    {
        return "$this->home/.dfr/$id";
    }

    /**
     * @return array<mixed> The content of the .thing.json file.
     */
    public function thingData(string $id): array
    {
        return (array) json_decode((string) file_get_contents($this->thingFolder($id).'/.thing.json'), true);
    }

    /**
     * Makes a git repository with one commit and returns its folder.
     */
    public function makeRepository(string $name = 'repository'): string
    {
        $folder = "$this->home/$name";
        mkdir($folder);

        foreach ([['init', '-q'], ['-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-q', '--allow-empty', '-m', 'first']] as $arguments) {
            (new Process(['git', ...$arguments], $folder, ['HOME' => $this->home]))->mustRun();
        }

        return $folder;
    }

    public function headHash(string $folder): string
    {
        return trim((new Process(['git', 'rev-parse', 'HEAD'], $folder))->mustRun()->getOutput());
    }
}
