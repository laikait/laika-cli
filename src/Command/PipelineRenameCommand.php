<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class PipelineRenameCommand implements CommandInterface
{
    public function signature(): string
    {
        return "rename:pipeline";
    }

    public function description(): string
    {
        return "Rename a pipeline and update its class";
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 2) {
            Message::error("Usage: php laika rename:pipeline [--old=name --new=name]");
            return 1;
        }

        // Get Old & New Pipeline Names
        $old = Argument::getValue('--old', $args);
        $new = Argument::getValue('--new', $args);

        // Check Onld & New Pipeline Exists
        if (!$old || !$new) {
            Message::error("Usage: php laika rename:pipeline [--old=name --new=name]");
            return 1;
        }

        // Check Valid Names
        if (!preg_match('/^[a-z_]+$/i', $old) || !preg_match('/^[a-z_]+$/i', $old)) {
            Message::error("Pipeline Name Should Contain Characters Only!");
            return 1;
        }

        $oldPath = $basePath . "/lf-app/Pipeline/{$old}.php";
        $newPath = $basePath . "/lf-app/Pipeline/{$new}.php";

        // Check Old Pipeline Exists
        if (!is_file($oldPath)) {
            Message::error("Pipeline [$old] Doesn't Exists!");
            return 1;
        }

        // Check New Pipeline Doesn't Exists
        if (is_file($newPath)) {
            Message::error("Pipeline [$new] Already Exists!");
            return 1;
        }

        try {
            $content = file_get_contents($oldPath);
            $content = preg_replace("/class[\s]+{$old}/i", "class {$new}", $content);

            rename($oldPath, $newPath);
            file_put_contents($newPath, $content);
            Message::success("Renamed Old Pipeline [{$old}] to New Pipeline [{$new}]");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }
}
