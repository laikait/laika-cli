<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class FilterRenameCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'rename:filter';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 2) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Old & New Filter Names
        $old = Argument::getValue('--old', $args);
        $new = Argument::getValue('--new', $args);

        // Validate Old & New Filter Name
        if (empty($old) || empty($new)) {
            Message::error("Old/New name should not be empty!");
            return 1;
        }

        if (!preg_match('/^[a-z_]+$/i', $old) || !preg_match('/^[a-z_]+$/i', $old)) {
            Message::error("Filter name should contain characters only!");
            return 1;
        }

        $oldPath = "{$basePath}/lf-app/Filter/{$old}.php";
        $newPath = "{$basePath}/lf-app/Filter/{$new}.php";

        // Check Old Filter exists
        if (!is_file($oldPath)) {
            Message::error("Filter [{$old}] doesn't exists.");
            return 1;
        }

        // Check New Filter doesn't exists
        if (is_file($newPath)) {
            Message::error("Filter [{$new}] already exists.");
            return 1;
        }

        try {
            $content = file_get_contents($oldPath);
            $content = preg_replace('/\bclass\s+' . preg_quote($old, '/') . '\b/i', "class {$new}", $content);

            rename($oldPath, $newPath);
            file_put_contents($newPath, $content);

            Message::success("Filter renamed [{$old} -> {$new}] successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika rename:filter <--old=name> <--new=name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Rename a filter class',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['old' => 'Old filter name', 'new' => 'New filter name']
        ];
    }
}
