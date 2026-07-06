<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use finfo;

use Laika\Service\Infra;

class ListMiddlewareCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:middleware';
    }

    public function description(): string
    {
        return 'List Registered Middleware CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            echo "Usage: php laika list:middleware";
            return 0;
        }

        $total = 0;

        $head = sprintf("%-4s | %-50s\n", '# SL', '# MIDDLEWARE CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getMiddlewareClasses() as $c) {
            $total++;
            printf("%-4s | %-50s\n", $total, $c);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }
}
