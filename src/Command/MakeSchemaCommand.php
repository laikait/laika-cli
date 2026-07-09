<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeSchemaCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:schema';
    }

    public function description(): string
    {
        return 'Create a new migration schema file';
    }

    public function handle(array $args, string $basePath): int
    {
        $table = $args[0] ?? null;

        if (!$table) {
            fwrite(STDERR, "Usage: laika make:schema <table>\n");
            return 1;
        }

        $timestamp = date('Y_m_d_His');
        $class = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $table))) . 'Table';
        $path = $basePath . "/database/migrations/{$timestamp}_create_{$table}_table.php";

        $content = Stub::render('schema', [
            'class' => $class,
            'table' => $table,
        ]);

        Stub::write($path, $content);
        echo "Schema created: database/migrations/{$timestamp}_create_{$table}_table.php\n";
        return 0;
    }

    public function command(): string
    {
        return "php laika make:schema <name>";
    }

    public function help(): string
    {
        return <<<HELP
        SCHEMA MAKE COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :
                name    :   Schema name to make

            PARAMETERS  :   No parameters available

        HELP;
    }
}
