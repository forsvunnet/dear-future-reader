<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

readonly class GitCommitHeader
{
    /**
     * @param  string  $time  The commit time in ISO 8601.
     * @param  string  $subject  The first line of the message.
     * @param  string  $body  The message after the subject.
     */
    public function __construct(
        public GitCommit $commit,
        public string $author,
        public string $time,
        public string $subject,
        public string $body,
    ) {}
}
