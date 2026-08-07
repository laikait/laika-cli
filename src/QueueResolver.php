<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Model\Connection;
use Laika\Queue\Driver\DatabaseDriver;
use Laika\Queue\Driver\DatabaseFailedJobProvider;
use Laika\Queue\Driver\JsonDriver;
use Laika\Queue\Driver\JsonFailedJobProvider;
use Laika\Queue\Driver\RedisDriver;
use Laika\Queue\Interfaces\FailedJobProviderInterface;
use Laika\Queue\Interfaces\QueueDriverInterface;

/**
 * Resolves the queue driver / failed-job provider from lf-config/queue.php
 * — same selection logic as vendor/laikait/laika-queue/bin/worker, kept
 * here so the queue:* CLI commands (retry, failed, flush) can reach the
 * driver/failer directly without spinning up a full Worker.
 */
class QueueResolver
{
    public static function driver(): QueueDriverInterface
    {
        $driverName = strtolower((string) config('queue', 'driver', 'database'));
        $connection = (string) config('queue', 'connection', 'default');

        if ($driverName === 'redis') {
            if (!class_exists(\Redis::class)) {
                throw new \RuntimeException("queue.driver is 'redis' but the redis extension isn't loaded.");
            }

            $redisConfig = [
                'host' => config('redis', 'host', '127.0.0.1'),
                'port' => config('redis', 'port', 6379),
                'auth' => config('redis', 'password') ?: null,
            ];
            $prefix = trim((string) config('redis', 'prefix', 'laika'), ':') . ':queue';

            return RedisDriver::fromConfig($redisConfig, $prefix);
        }

        if ($driverName === 'json') {
            return new JsonDriver();
        }

        Connection::add(config('database', $connection));
        $driver = new DatabaseDriver($connection);
        $driver->ensureSchema();
        return $driver;
    }

    public static function failedProvider(): FailedJobProviderInterface
    {
        $driverName = strtolower((string) config('queue', 'driver', 'database'));
        $connection = (string) config('queue', 'connection', 'default');
        $failedDriverName = strtolower((string) config(
            'queue',
            'failed_driver',
            $driverName === 'database' ? 'database' : 'json'
        ));

        if ($failedDriverName === 'database') {
            Connection::add(config('database', $connection));
            $failer = new DatabaseFailedJobProvider($connection);
            $failer->ensureSchema();
            return $failer;
        }

        return new JsonFailedJobProvider();
    }
}
