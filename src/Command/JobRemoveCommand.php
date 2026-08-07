<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class JobRemoveCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'job:remove';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
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

        $path = "{$basePath}/lf-app/Job/{$name}.php";

        if (!is_file($path)) {
            Message::error("Job [{$name}] not found!");
            return 1;
        }

        try {
            // Confirm
            $action = Argument::readline("Confirm remove [App\\Job\\{$name}]?");
            if (!$action) {
                Message::warning("Job [{$name}] remove canceled!", 'console');
                return 0;
            }
            if (!unlink($path)) {
                Message::error("Job [{$name}] remove failed!");
                return 1;
            }

            Message::success("Job [{$name}] removed successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika job:remove <name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Remove a job class',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' =>  'Job name to remove'],
            'params'        =>  []
        ];
    }
}
