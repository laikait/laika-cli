<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class ListControllerCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:controller';
    }

    public function description(): string
    {
        return 'List Registered Controller CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            echo "Usage: php laika list:controller";
            return 1;
        }

        $total = 0;

        $head = sprintf("%-4s | %-50s\n", '# SL', '# CONTROLLER CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getControllerClasses() as $c) {
            $total++;
            printf("%-4s | %-50s\n", $total, $c);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }
}
