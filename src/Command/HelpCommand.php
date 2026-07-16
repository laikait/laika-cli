<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;
use Laika\Service\Directory;

class HelpCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'help';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $files = Directory::files(__DIR__);
        $classes = array_map(fn ($f) => "\\Laika\\Cli\\Command\\" . pathinfo($f, PATHINFO_FILENAME), $files);
        $list = [];
        foreach ($classes as $c) {
            if (method_exists($c, 'help') && ($c != '\Laika\Cli\Command\CommandInterface')) {
                $list[] = $c;
            }
        }
        
        Message::info('COMMAND HELP');
        echo "--------------------------------\n";
        foreach ($list as $class) {
            $arr = (new $class())->help();
            echo "SIGNATURE\t:\t" . Message::txt_yellow($arr['signature']) . "\n";
            echo "COMMAND\t\t:\t" . Message::txt_cyan($arr['command']) . "\n";
            echo "DESCRIPTION\t:\t{$arr['description']}\n";
            $len = 10;
            if (!empty($arr['inputs'])) {
                echo "INPUTS:\n";
                foreach ($arr['inputs'] as $k => $v) {
                    $s = str_repeat(' ', $len - strlen($k));
                    echo "\t{$k}{$s}:\t{$v}\n";
                }
            }
            if (!empty($arr['params'])) {
                echo "PARAMS:\n";
                foreach ($arr['params'] as $k => $v) {
                    $s = str_repeat(' ', $len - strlen("--{$k}"));
                    echo "\t--{$k}{$s}:\t{$v}\n";
                }
            }
            echo "--------------------------------------------------------------------------\n\n";
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika help";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Laika command help',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['name' => 'Help signature name']
        ];
    }
}
