<?php

declare(strict_types=1);

it('adds to the start of the body and keeps the frontmatter first', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'log.md'], "---\ndescription: The log\n---\nold line\n");
    $this->dfr([$id, 'write:prepend', 'log.md', 'new line']);

    expect(file_get_contents($this->thingFolder($id).'/log.md'))
        ->toBe("---\ndescription: The log\n---\nnew line\nold line\n");
});

it('adds to the start of a file without frontmatter', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'log.md', 'old line']);
    $this->dfr([$id, 'write:prepend', 'log.md', 'new line']);

    expect(file_get_contents($this->thingFolder($id).'/log.md'))->toBe("new line\nold line\n");
});
