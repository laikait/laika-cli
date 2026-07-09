<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class ListSchemaCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:schema';
    }

    public function description(): string
    {
        return 'List Registered Schema CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 0;
        }

        $total = 0;

        $head = sprintf("%-20s | %-50s\n", '# TABLE NAME', '# SCHEMA CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getSchemaClasses() as $t => $c) {

                printf("%-20s | %-50s\n", $t, $c);
                $total++;
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }

    public function command(): string
    {
        return "php laika list:schema";
    }

    public function help(): string
    {
        return <<<HELP
        SCHEMA LIST COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :   No inputs available

            PARAMETERS  :   No parameters available

        HELP;
    }
}
