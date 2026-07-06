<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use finfo;

use Laika\Service\Infra;

class ListAfterwareCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:afterware';
    }

    public function description(): string
    {
        return 'List Registered Afterware CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            echo "Usage: php laika list:afterware";
            return 0;
        }

        $total = 0;

        $head = sprintf("%-4s | %-50s\n", '# SL', '# AFTERWARE CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getAfterwareClasses() as $c) {
            $total++;
            printf("%-4s | %-50s\n", $total, $c);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }
}
