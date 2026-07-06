<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class RenameRouteCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'rename:route';
    }

    public function description(): string
    {
        return 'Rename a route file';
    }

    public function handle(array $args, string $basePath): int
    {
        $old = $args[0] ?? null;
        $new = $args[1] ?? null;

        if (!$old || !$new) {
            fwrite(STDERR, "Usage: laika rename:route <old> <new>\n");
            return 1;
        }

        $oldPath = $basePath . "/lf-routes/{$old}.php";
        $newPath = $basePath . "/lf-routes/{$new}.php";

        if (!is_file($oldPath)) {
            fwrite(STDERR, "Not found: {$oldPath}\n");
            return 1;
        }

        if (is_file($newPath)) {
            fwrite(STDERR, "Already exists: {$newPath}\n");
            return 1;
        }

        rename($oldPath, $newPath);
        echo "Renamed: {$old} -> {$new}\n";
        return 0;
    }
}
