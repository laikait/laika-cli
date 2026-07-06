<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class ListCommand implements CommandInterface
{
    public function __construct(protected string $type, protected string $dir, protected string $ext = 'php')
    {
    }

    public function signature(): string
    {
        return "list:{$this->type}";
    }

    public function description(): string
    {
        return "List all {$this->type}";
    }

    public function handle(array $args, string $basePath): int
    {
        $path = $basePath . '/' . $this->dir;

        if (!is_dir($path)) {
            echo "No {$this->type} found.\n";
            return 0;
        }

        $files = glob($path . '/*.' . $this->ext);

        if (!$files) {
            echo "No {$this->type} found.\n";
            return 0;
        }

        foreach ($files as $file) {
            echo '  ' . basename($file, '.' . $this->ext) . "\n";
        }

        return 0;
    }
}
