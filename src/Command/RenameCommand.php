<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class RenameCommand implements CommandInterface
{
    public function __construct(
        protected string $type,
        protected string $dir,
        protected string $namespace,
        protected string $ext = 'php'
    ) {
    }

    public function signature(): string
    {
        return "rename:{$this->type}";
    }

    public function description(): string
    {
        return "Rename a {$this->type} and update its class/namespace";
    }

    public function handle(array $args, string $basePath): int
    {
        $old = $args[0] ?? null;
        $new = $args[1] ?? null;

        if (!$old || !$new) {
            fwrite(STDERR, "Usage: laika rename:{$this->type} <Old> <New>\n");
            return 1;
        }

        $oldPath = $basePath . '/' . $this->dir . '/' . $old . '.' . $this->ext;
        $newPath = $basePath . '/' . $this->dir . '/' . $new . '.' . $this->ext;

        if (!is_file($oldPath)) {
            fwrite(STDERR, "Not found: {$oldPath}\n");
            return 1;
        }

        if (is_file($newPath)) {
            fwrite(STDERR, "Already exists: {$newPath}\n");
            return 1;
        }

        $content = file_get_contents($oldPath);
        $content = str_replace("class {$old}", "class {$new}", $content);

        rename($oldPath, $newPath);
        file_put_contents($newPath, $content);

        echo "Renamed: {$old} -> {$new}\n";
        return 0;
    }
}
