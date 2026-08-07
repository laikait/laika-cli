<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class PipelineRemoveCommand implements CommandInterface
{
    public function signature(): string
    {
        return "pipeline:remove";
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Pipeline Name
        $pipeline = $args[0];

        $path = $basePath . "/lf-app/Pipeline/{$pipeline}.php";

        // Check Old Pipeline Exists
        if (!is_file($path)) {
            Message::error("Pipeline [$pipeline] Doesn't Exists!");
            return 1;
        }

        try {
            unlink($path);
            Message::success("Pipeline [{$pipeline}] Removed Successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika pipeline:remove <name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Remove a pipeline',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Pipeline name to remove'],
            'params'        =>  []
        ];
    }
}
