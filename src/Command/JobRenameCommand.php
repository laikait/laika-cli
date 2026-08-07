<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class JobRenameCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'job:rename';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 2) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Old & New Job Names
        $old = Argument::getValue('--old', $args);
        $new = Argument::getValue('--new', $args);

        // Validate Old & New Job Name
        if (empty($old) || empty($new)) {
            Message::error("Old/New name should not be empty!");
            return 1;
        }

        if (!preg_match('/^[a-z_]+$/i', $old) || !preg_match('/^[a-z_]+$/i', $new)) {
            Message::error("Job name should contain characters only!");
            return 1;
        }

        $oldPath = "{$basePath}/lf-app/Job/{$old}.php";
        $newPath = "{$basePath}/lf-app/Job/{$new}.php";

        // Check Old Job Exists
        if (!is_file($oldPath)) {
            Message::error("Job [{$old}] doesn't exists.");
            return 1;
        }

        // Check New Job Doesn't Exists
        if (is_file($newPath)) {
            Message::error("Job [{$new}] already exists.");
            return 1;
        }

        // Confirm From User
        if (!Argument::readline("Continue [{$old} -> {$new}]?")) {
            Message::warning("Canceled by user!", 'console');
            return 0;
        }

        try {
            $content = file_get_contents($oldPath);
            $content = preg_replace('/\bclass\s+' . preg_quote($old, '/') . '\b/', "class {$new}", $content);

            rename($oldPath, $newPath);
            file_put_contents($newPath, $content);

            Message::success("Job renamed [{$old} -> {$new}] successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika job:rename <--old=name> <--new=name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Rename a job class',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['old' => 'Old job name', 'new' => 'New job name']
        ];
    }
}
