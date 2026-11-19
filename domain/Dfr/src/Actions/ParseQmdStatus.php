<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\QmdIndexState;

/**
 * Reads the state of the index from the text of `qmd status`. qmd has no JSON for the status.
 */
class ParseQmdStatus
{
    public function __invoke(string $status): QmdIndexState
    {
        return new QmdIndexState(
            made: preg_match('#^\s+things \(qmd://things/\)#m', $status) === 1,
            files: preg_match('/Total:\s+(\d+) files indexed/', $status, $files) === 1 ? (int) $files[1] : 0,
            // qmd writes the Pending line only when one or more files wait.
            pending: preg_match('/Pending:\s+(\d+) need embedding/', $status, $pending) === 1 ? (int) $pending[1] : 0,
            updated: preg_match('/^\s+Updated:\s+(.+)$/m', $status, $updated) === 1 ? trim($updated[1]) : null,
        );
    }
}
