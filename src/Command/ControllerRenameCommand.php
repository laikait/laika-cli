<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Stub;

class ControllerRenameCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'controller:rename';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 2) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Old & New Controller Names
        $old = Argument::getValue('--old', $args);
        $new = Argument::getValue('--new', $args);

        // Validate Old & New Scontroller Name
        if (empty($old) || empty($new)) {
            Message::error("Old/New name should not be empty!");
            return 1;
        }

        if (!preg_match('/^[a-z_]+$/i', $old) || !preg_match('/^[a-z_]+$/i', $new)) {
            Message::error("Controller name should contain characters only!");
            return 1;
        }

        // Confirm From User
        if (!Argument::readline("Continue [{$old} -> {$new}]?")) {
            Message::warning("Canceled by user!", 'console');
            return 0;
        }

        // Get Paths
        $oldPath = "{$basePath}/lf-app/Controller/{$old}.php";
        $newPath = "{$basePath}/lf-app/Controller/{$new}.php";

        // Check Old Controller Exists
        if (!is_file($oldPath)) {
            Message::error("Controller [{$old}] doesn't exists!");
            return 1;
        }

        // Check New Controller Doesn't Exists
        if (is_file($newPath)) {
            Message::error("Controller [{$new}] already exists!");
            return 1;
        }

        try {
            // Get Controller Content
            $content = file_get_contents($oldPath);
            $content = preg_replace('/\bclass\s+' . preg_quote($old, '/') . '\b/', "class {$new}", $content);

            rename($oldPath, $newPath);
            file_put_contents($newPath, $content);
            Message::success("Controller [{$old} -> {$new}] renamed successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika controller:rename [--old=name] [--new=name]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Rename a controller class.',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['old' => 'Old controller name', 'new' => 'New controller name']
        ];
    }
}
