<?php

declare(strict_types=1);

it('adds to the end, with a line break when the file has none at the end', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'log.md'], 'first');
    $this->dfr([$id, 'write:append', 'log.md', 'second']);

    expect(file_get_contents($this->thingFolder($id).'/log.md'))->toBe("first\nsecond\n");
});

it('makes the file when it does not exist', function () {
    $id = $this->init();
    $this->dfr([$id, 'write:append', 'log.md', 'first']);

    expect(file_get_contents($this->thingFolder($id).'/log.md'))->toBe("first\n");
});
