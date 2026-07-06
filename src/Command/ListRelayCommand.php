<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use finfo;

use Laika\Service\Infra;

class ListRelayCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:relay';
    }

    public function description(): string
    {
        return 'List Registered Relay CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            echo "Usage: php laika list:relay";
            return 0;
        }

        $total = 0;

        $head = sprintf("%-25s | %-50s\n", '# RELAY NAME', '# RELAY CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getRelayClasses() as $name => $class) {
            $total++;
            printf("%-25s | %-50s\n", $name, $class);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }
}
