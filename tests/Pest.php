<?php

declare(strict_types=1);

use Domain\Dfr\Tests\DfrTestCase;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');
pest()->extend(DfrTestCase::class)->in('../domain/Dfr/tests/Feature');
