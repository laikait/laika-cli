<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Core\Worker\Queue;

class QueueFailedCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'queue:failed';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $queue = Argument::getValue('queue', $args);

        try {
            $failed = Queue::failedProvider('database')->all($queue ?: null);
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        if (empty($failed)) {
            Message::info("No failed jobs found!");
            return 0;
        }

        $rows = [];
        foreach ($failed as $r) {
            // First line only, capped — a full stack trace here would
            // stretch every row in the table out to match it.
            $exception = strtok((string) ($r['exception'] ?? ''), "\n") ?: '';
            if (strlen($exception) > 60) {
                $exception = substr($exception, 0, 57) . '...';
            }

            $rows[] = [
                count($rows) + 1,
                $r['id'] ?? '',
                $r['queue'] ?? '',
                isset($r['failed_at']) ? date('Y-m-d H:i:s', (int) $r['failed_at']) : '',
                $exception,
            ];
        }

        Table::render('FAILED JOBS', ['# SL', '# ID', '# QUEUE', '# FAILED AT', '# EXCEPTION'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika queue:failed [--queue=name]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List failed jobs',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['queue' => 'Only list failed jobs from this queue']
        ];
    }
}
