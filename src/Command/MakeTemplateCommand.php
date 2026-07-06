<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class MakeTemplateCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:template';
    }

    public function description(): string
    {
        return 'Create a new Twig template view';
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika make:template <path/name>\n");
            return 1;
        }

        $path = $basePath . "/resources/views/{$name}.twig";

        $content = Stub::render('template.twig', [
            'title' => basename($name),
        ]);

        Stub::write($path, $content);
        echo "Template created: resources/views/{$name}.twig\n";
        return 0;
    }
}
