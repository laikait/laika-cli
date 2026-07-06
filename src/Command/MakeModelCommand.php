<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeModelCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:model';
    }

    public function description(): string
    {
        return 'Create a new model class';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:model <Name> [--table=name] [--schema]\n");
            return 1;
        }

        $table = null;
        $withSchema = false;

        foreach (array_slice($args, 1) as $arg) {
            if (str_starts_with($arg, '--table=')) {
                $table = substr($arg, 8);
            }
            if ($arg === '--schema') {
                $withSchema = true;
            }
        }

        $table ??= strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';

        $path = $basePath . "/app/Model/{$name}.php";

        $content = Stub::render('model', [
            'class' => $name,
            'table' => $table,
        ]);

        Stub::write($path, $content);
        echo "Model created: app/Model/{$name}.php\n";

        if ($withSchema) {
            (new MakeSchemaCommand())->handle([$table], $basePath);
        }

        return 0;
    }
}
