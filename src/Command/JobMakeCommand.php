<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Stub;

class JobMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'job:make';
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(1, 3))) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Job Name & Validate
        $name = $args[0];
        if (empty($name)) {
            Message::error("Job name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid job name [{$name}].");
            return 1;
        }

        // Get Queue Name & Validate
        $queue = Argument::getValue('queue', $args, 'default');
        if (empty($queue)) {
            Message::error("Queue name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $queue)) {
            Message::error("Invalid queue name [{$queue}].");
            return 1;
        }

        // Get Max Tries & Validate
        $maxTries = Argument::getValue('max', $args, 3);
        if (!preg_match('/^[0-9]+$/', (string) $maxTries) || (int) $maxTries < 1) {
            Message::error("Invalid max tries [{$maxTries}]. It should be a positive integer.");
            return 1;
        }

        $path = $basePath . "/lf-app/Job/{$name}.php";

        // Check Job Doesn't Exists
        if (is_file($path)) {
            Message::error("Job [{$name}] already exists.");
            return 1;
        }

        try {
            $content = Stub::render('job', [
                'class'     =>  $name,
                'queue'     =>  $queue,
                'maxTries'  =>  (string) (int) $maxTries,
            ]);

            Stub::write($path, $content);
            Message::success("Job [{$name}] created successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika job:make <name> [--queue=default] [--max=3]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new job class',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Job class name'],
            'params'        =>  [
                                    'queue'     =>  'Queue name the job belongs to',
                                    'maxTries'  =>  'Maximum retry attempts',
                                ]
        ];
    }
}
