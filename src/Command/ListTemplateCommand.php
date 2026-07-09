<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class ListTemplateCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:template';
    }

    public function description(): string
    {
        return 'List Template Files';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 0) {
            Message::suggestion($this->command());
            return 0;
        }

        $total = 0;

        $head = sprintf("%-20s | %-20s | %-15s\n", '# PATH', '# TEMPLATE NAME', '# EXTENSION');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach (Infra::getTemplateNames() as $path => $sets) {
            $total++;
            foreach ($sets as $templates) {
                foreach ($templates as $ext => $name) printf("%-20s | %-20s | %-15s\n", $path, $name, $ext);
            }
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
        TEMPLATE LIST COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :   No inputs available

            PARAMETERS  :   No parameters available

        HELP;
    }
}
