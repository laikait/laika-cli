<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class AfterwareListCommand implements CommandInterface
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
        if (count($args) != 0) {
            Message::info($this->commandSample());
            return 1;
        }

        $total = 0;

        $afterwares = Infra::getAfterwareClasses();

        if (empty($afterwares)) {
            Message::info("No Afterwares Found!");
            return 0;
        }

        $head = sprintf("%-4s | %-50s\n", Message::txt_yellow('# SL'), Message::txt_yellow('# AFTERWARE CLASS'));
        echo "\n" . str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach ($afterwares as $c) {
            $total++;
            printf("%-4s | %-50s\n", $total, $c);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }

    public function command(): string
    {
        return "php laika list:filter";
    }

    public function help(): string
    {
        return <<<HELP
        FILTER LIST COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :   No inputs available

            PARAMETERS  :   No parameters available

        HELP;
    }
}
