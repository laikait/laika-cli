<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class ControllerListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:controller';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $controllers = Infra::getControllerClasses();

        // Check Controllers Exists
        if (empty($controllers)) {
            Message::info("No controller found!");
            return 0;
        }

        $total = 0;

        $head = sprintf("%-4s | %-50s\n", '# SL', '# CONTROLLER CLASSES');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach ($controllers as $c) {
            $total++;
            printf("%-4s | %-50s\n", $total, $c);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }

    public function command(): string
    {
        return "php laika list:controller";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered controller cLasses',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
