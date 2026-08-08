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
 * Resolves the queue driver / failed-job provider from the QUEUE_* / REDIS_*
 * environment variables — same selection logic as
 * vendor/laikait/laika-queue/bin/worker, kept here so the queue:* CLI
 * commands (retry, failed, flush) can reach the driver/failer directly
 * without spinning up a full Worker.
 */
class QueueResolver
{
    public static function driver(): QueueDriverInterface
    {
        $driverName = strtolower((string) env('QUEUE_DRIVER', 'database'));
        $connection = (string) env('QUEUE_CONNECTION', 'default');

        if ($driverName === 'redis') {
            if (!class_exists(\Redis::class)) {
                throw new \RuntimeException("QUEUE_DRIVER is 'redis' but the redis extension isn't loaded.");
            }

            $redisConfig = [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => (int) env('REDIS_PORT', 6379),
                'auth' => env('REDIS_PASSWORD') ?: null,
            ];
            $prefix = trim((string) env('REDIS_PREFIX', 'laika'), ':') . ':queue';

            return RedisDriver::fromConfig($redisConfig, $prefix);
        }

        if ($driverName === 'json') {
            return new JsonDriver();
        }

        Connection::add(self::databaseConfig(), $connection);
        $driver = new DatabaseDriver($connection);
        $driver->ensureSchema();
        return $driver;
    }

    public static function failedProvider(): FailedJobProviderInterface
    {
        $driverName = strtolower((string) env('QUEUE_DRIVER', 'database'));
        $connection = (string) env('QUEUE_CONNECTION', 'default');
        $failedDriverName = strtolower((string) (env('QUEUE_FAILED_DRIVER') ?? ($driverName === 'database' ? 'database' : 'json')));

        if ($failedDriverName === 'database') {
            Connection::add(self::databaseConfig(), $connection);
            $failer = new DatabaseFailedJobProvider($connection);
            $failer->ensureSchema();
            return $failer;
        }

        return new JsonFailedJobProvider();
    }

    /**
     * Database config for the queue's 'database' driver/failed-provider.
     * Only the DB_* environment variables (the 'default' connection's
     * shape) are supported here — there's no generic env-var scheme for
     * an arbitrary number of named connections.
     */
    private static function databaseConfig(): array
    {
        return [
            'driver'   => env('DB_DRIVER', 'mysql'),
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => (int) env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];
    }
}
