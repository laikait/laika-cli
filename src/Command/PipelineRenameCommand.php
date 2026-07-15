<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class PipelineRenameCommand implements CommandInterface
{
    public function signature(): string
    {
        return "rename:pipeline";
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 2) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Old & New Pipeline Names
        $old = Argument::getValue('--old', $args);
        $new = Argument::getValue('--new', $args);

        // Check Old & New Names are Not Empty
        if (empty($old) || empty($new)) {
            Message::error("Old/New name should not be empty!");
            return 1;
        }

        // Check Valid Names
        if (!preg_match('/^[a-z_]+$/i', $old) || !preg_match('/^[a-z_]+$/i', $old)) {
            Message::error("Old/New Name Should Contain Characters Only!");
            return 1;
        }

        $oldPath = $basePath . "/lf-app/Pipeline/{$old}.php";
        $newPath = $basePath . "/lf-app/Pipeline/{$new}.php";

        // Check Old Pipeline Exists
        if (!is_file($oldPath)) {
            Message::error("Pipeline [{$old}] Doesn't Exists!");
            return 1;
        }

        // Check New Pipeline Doesn't Exists
        if (is_file($newPath)) {
            Message::error("Pipeline [{$new}] Already Exists!");
            return 1;
        }

        try {
            $content = file_get_contents($oldPath);
            $content = preg_replace("/\bclass\s+" . preg_quote($old, '/') . "\b/i", "class {$new}", $content);

            rename($oldPath, $newPath);
            file_put_contents($newPath, $content);
            Message::success("Renamed Pipeline: [{$old} -> {$new}]");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika rename:pipeline <--old=name> <--new=name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Rename a pipeline and update its class',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['old' => '(Required) old pipeline name', 'new' => '(Required) new pipeline name']
        ];
    }
}
