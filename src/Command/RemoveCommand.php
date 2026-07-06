<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class RemoveCommand implements CommandInterface
{
    public function __construct(protected string $type, protected string $dir, protected string $ext = 'php')
    {
    }

    public function signature(): string
    {
        return "remove:{$this->type}";
    }

    public function description(): string
    {
        return "Remove a {$this->type}";
    }

    public function handle(array $args, string $basePath): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            fwrite(STDERR, "Usage: laika remove:{$this->type} <name>\n");
            return 1;
        }

        $path = $basePath . '/' . $this->dir . '/' . $name . '.' . $this->ext;

        if (!is_file($path)) {
            fwrite(STDERR, "Not found: {$path}\n");
            return 1;
        }

        unlink($path);
        echo "Removed: {$this->dir}/{$name}.{$this->ext}\n";
        return 0;
    }
}
