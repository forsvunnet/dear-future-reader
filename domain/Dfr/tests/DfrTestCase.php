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
     */
    public function dfr(array $arguments, ?string $input = null): Process
    {
        $process = new Process([base_path('dfr'), ...$arguments], $this->home, ['HOME' => $this->home], $input);
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
}
