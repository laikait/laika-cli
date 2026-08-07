<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\QueueResolver;

class QueueFlushCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'queue:flush';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $hours = Argument::getValue('hours', $args);
        if ($hours !== null && !preg_match('/^[0-9]+$/', (string) $hours)) {
            Message::error("Invalid hours value [{$hours}]. It should be a positive integer.");
            return 1;
        }

        $message = $hours
            ? "Confirm clearing failed jobs older than {$hours} hour(s)?"
            : "Confirm clearing ALL failed jobs?";

        if (!Argument::readline($message)) {
            Message::warning("Canceled by user!", 'console');
            return 0;
        }

        try {
            QueueResolver::failedProvider()->flush($hours !== null ? (int) $hours : null);
            Message::success($hours ? "Cleared failed jobs older than {$hours} hour(s)." : "Cleared all failed jobs.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika queue:flush [--hours=N]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Clear failed jobs',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['hours' => 'Only clear failed jobs older than N hours']
        ];
    }
}
