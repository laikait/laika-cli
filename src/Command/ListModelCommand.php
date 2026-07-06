<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use finfo;

use Laika\Service\Infra;

class ListModelCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:model';
    }

    public function description(): string
    {
        return 'List Registered Model CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            echo "Usage: php laika list:model";
            return 0;
        }

        $total = 0;

        $head = sprintf("%-20s | %-50s\n", '# TABLE NAME', '# MODEL CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getModelClasses() as $t => $c) {

                printf("%-20s | %-50s\n", $t, $c);
                $total++;
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }
}
