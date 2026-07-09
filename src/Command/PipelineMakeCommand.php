<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class PipelineMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:pipeline';
    }

    public function description(): string
    {
        return 'Create a new pipeline class';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::error("Usage: php laika make:pipeline <name>");
            return 1;
        }

        // Check Pipeline Name
        $name = $args[0];
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid Pipeline Name [{$name}]!");
            return 1;
        }

        $path = $basePath . "/lf-app/Pipeline/{$name}.php";

        // Check Pipline is Not Exists
        if (is_file($path)) {
            Message::error("Pipeline [{$name}] Already Exists!");
            return 1;
        }

        try {
            // Get Stb Content
            $content = Stub::render('pipeline', [
                'class' => $name,
            ]);

            // Stub Write
            Stub::write($path, $content);
            Message::success("Pipeline [{$name}] Created Successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }
}
