<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeAfterwareCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:filter';
    }

    public function description(): string
    {
        return 'Create a new filter class';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:afterware <Name>\n");
            return 1;
        }

        $path = $basePath . "/app/Afterware/{$name}.php";

        $content = Stub::render('afterware', [
            'class' => $name,
        ]);

        Stub::write($path, $content);

        echo "Afterware created: app/Afterware/{$name}.php\n";
        return 0;
    }

    public function command(): string
    {
        return "php laika make:filter <name>";
    }

    public function help(): string
    {
        return <<<HELP
        FILTER MAKE COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :
                name    :   Filter name to make

            PARAMETERS  :   No parameters available

        HELP;
    }
}
