<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeMiddlewareCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:middleware';
    }

    public function description(): string
    {
        return 'Create a new middleware class';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:middleware <Name>\n");
            return 1;
        }

        $path = $basePath . "/app/Middleware/{$name}.php";

        $content = Stub::render('middleware', [
            'class' => $name,
        ]);

        Stub::write($path, $content);

        echo "Middleware created: app/Middleware/{$name}.php\n";
        return 0;
    }
}
