<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Service\Infra;
use Laika\Core\Worker\Queue;
use Laika\Queue\Abstracts\Job;

class QueueRetryCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'queue:retry';
    }

    public function handle(array $args, string $basePath): int
    {
        if (empty($args)) {
            Message::suggestion($this->command());
            return 1;
        }

        $all = Argument::getBool('all', $args);
        $id = $all ? null : ($args[0] ?? null);
        $queueFilter = Argument::getValue('queue', $args);

        if (!$all && empty($id)) {
            Message::error("Provide a failed job [id] or use --all.");
            return 1;
        }

        try {
            // Restoring a stored payload back into a Job object goes
            // through the same trusted-class allow-list as the worker —
            // see Job::unserializePayload(). Register every Job subclass
            // under lf-app/Job here too, same as bin/worker does on start.
            Job::registerTrustedClasses(Infra::getQueueJobsClasses());

            $failer = Queue::failedProvider('database');
            $driver = Queue::driver('database');
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        $rows = $all
            ? $failer->all($queueFilter ?: null)
            : array_filter([$failer->find($id)]);

        if (empty($rows)) {
            Message::error($all ? "No failed jobs found!" : "Failed job [{$id}] not found!");
            return 1;
        }

        $retried = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            try {
                $job = Job::unserializePayload($row['payload']);
                // Start the retry with a clean attempt count — some drivers
                // (RedisDriver) increment the payload's own $tries on pop()
                // rather than deriving it fresh from a stored counter, so a
                // job retried with its old maxTries-exhausted $tries still
                // in place could fail permanently on its very next attempt.
                $job->tries = 0;

                $driver->push($job, $row['queue']);
                $failer->forget($row['id']);
                $retried++;
                echo "Retried [{$row['id']}] -> queue [{$row['queue']}]\n";
            } catch (\Throwable $th) {
                $skipped++;
                Message::error("Skipped [{$row['id']}]: {$th->getMessage()}");
            }
        }

        Message::success("Retried {$retried} job(s)." . ($skipped ? " Skipped {$skipped}." : ''));

        return ($skipped > 0 && $retried === 0) ? 1 : 0;
    }

    public function command(): string
    {
        return "php laika queue:retry <id>|--all [--queue=name]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Re-push failed job(s) back onto their queue',
            'command'       =>  $this->command(),
            'inputs'        =>  ['id' => 'Failed job id to retry'],
            'params'        =>  [
                                    'all'   =>  'Retry every failed job',
                                    'queue' =>  'Only retry failed jobs from this queue (with --all)',
                                ]
        ];
    }
}
