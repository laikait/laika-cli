<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Service\Infra;

class ControllerListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'controller:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $controllers = Infra::getControllerClasses();

        if (empty($controllers)) {
            Message::info("No controllers found!");
            return 0;
        }

        $rows = [];
        foreach ($controllers as $c) {
            $rows[] = [count($rows) + 1, $c];
        }

        Table::render('CONTROLLER CLASSES', ['# SL', '# CONTROLLER CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika controller:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered controller classes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
