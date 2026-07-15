<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class RelayListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:relay';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
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

    public function command(): string
    {
        return "php laika list:relay";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered relay classes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
