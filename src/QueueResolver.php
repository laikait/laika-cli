<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Core\Worker\Queue;
use Laika\Queue\Interfaces\FailedJobProviderInterface;
use Laika\Queue\Interfaces\QueueDriverInterface;

/**
 * Resolves the queue driver / failed-job provider from lf-config/queue.php
 * — same selection logic as vendor/laikait/laika-queue/bin/worker, kept
 * here so the queue:* CLI commands (retry, failed, flush) can reach the
 * driver/failer directly without spinning up a full Worker.
 *
 * The selection itself lives in Laika\Core\Worker\Queue; this is a thin
 * alias that keeps the CLI's historical default of 'database'.
 */
class QueueResolver
{
    public static function driver(): QueueDriverInterface
    {
        return Queue::driver('database');
    }

    public static function failedProvider(): FailedJobProviderInterface
    {
        return Queue::failedProvider('database');
    }
}
