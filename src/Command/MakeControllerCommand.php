<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeControllerCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:controller';
    }

    public function description(): string
    {
        return 'Create a new controller class';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:controller <Name>\n");
            return 1;
        }

        $path = $basePath . "/app/Controller/{$name}.php";

        $content = Stub::render('controller', [
            'class' => $name,
        ]);

        Stub::write($path, $content);
        echo "Controller created: app/Controller/{$name}.php\n";
        return 0;
    }
}
