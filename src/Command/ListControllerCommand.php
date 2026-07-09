<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class ListControllerCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:response';
    }

    public function description(): string
    {
        return 'List Registered response CLasses';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
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

    public function command(): string
    {
        return "php laika list:template";
    }

    public function help(): string
    {
        return <<<HELP
        RESPONSE LIST COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :   No inputs available

            PARAMETERS  :   No parameters available

        HELP;
    }
}
