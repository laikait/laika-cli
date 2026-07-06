<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeServiceCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:service';
    }

    public function description(): string
    {
        return 'Create a new service class';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:service <Name>\n");
            return 1;
        }

        $path = $basePath . "/app/Service/{$name}.php";

        $content = Stub::render('service', [
            'class' => $name,
        ]);

        Stub::write($path, $content);
        echo "Service created: app/Service/{$name}.php\n";
        return 0;
    }
}
