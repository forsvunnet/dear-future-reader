<?php

declare(strict_types=1);
use Symfony\Component\Process\Process;

it('writes from arguments as one line', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'Build', 'the login page']);

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("Build the login page\n");
});

it('writes from a pipe as it is', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "line one\nline two");

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("line one\nline two");
});

it('uses only the arguments when it gets both', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'from arguments'], 'from pipe');

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("from arguments\n");
});

it('overwrites the file', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'old']);
    $this->dfr([$id, 'write', 'plan.md', 'new']);

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("new\n");
});

it('fails on a filename that is a path or starts with a dot', function (string $fileName) {
    $id = $this->init();
    $process = $this->dfr([$id, 'write', $fileName, 'text']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('is not a valid filename');
})->with(['../escape.md', 'sub/plan.md', '.thing.json']);

it('keeps the files of writes that run at the same time', function () {
    $id = $this->init();
    $processes = [];

    foreach (range(1, 10) as $number) {
        $process = new Process([base_path('dfr'), $id, 'write', "file-$number.md", 'text'], null, ['HOME' => $this->home]);
        $process->start();
        $processes[] = $process;
    }

    foreach ($processes as $process) {
        $process->wait();
    }

    $data = json_decode((string) file_get_contents($this->thingFolder($id).'/.thing.json'), true);

    expect($data['files'])->toHaveCount(10);
});

it('does not wait for the pipe when the content comes as arguments', function () {
    $id = $this->init();
    // A pipe that stays open with no data, like the stdin of some agent shells.
    $process = new Symfony\Component\Process\Process(
        [base_path('dfr'), $id, 'write', 'plan.md', 'the', 'plan'],
        $this->home,
        ['HOME' => $this->home, 'CLAUDE_CODE_SESSION_ID' => false],
        new Symfony\Component\Process\InputStream,
        10,
    );
    $process->run();

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("the plan\n");
});
