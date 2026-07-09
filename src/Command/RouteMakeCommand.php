<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class RouteMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:route';
    }

    public function description(): string
    {
        return 'Create a new route file in lf-routes';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:route <name>\n");
            return 1;
        }

        $path = $basePath . "/lf-routes/{$name}.php";

        $content = Stub::render('route', [
            'name' => $name,
        ]);

        Stub::write($path, $content);

        echo "Route created: lf-routes/{$name}.php\n";
        return 0;
    }

    public function command(): string
    {
        return "php laika make:route <name>";
    }

    public function help(): string
    {
        return <<<HELP
        ROUTE MAKE COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :
                name    :   Name of the route
            
            PARAMETERS  :   No parameters available
        HELP;
    }
}
