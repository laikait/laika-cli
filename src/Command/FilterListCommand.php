<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class FilterListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:filter';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $total = 0;

        $filters = Infra::getFilterClasses();

        if (empty($filters)) {
            Message::info("No filters found!");
            return 1;
        }

        $head = sprintf("%-4s | %-50s\n", Message::txt_yellow('# SL'), Message::txt_yellow('# FILTER CLASSES'));
        echo "\n" . str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach ($filters as $f) {
            $total++;
            printf("%-4s | %-50s\n", $total, $f);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }

    public function command(): string
    {
        return "php laika list:filter";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registers filter cLasses',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
