<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Service\Infra;

class RelayListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'relay:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $relays = Infra::getRelayClasses();

        if (empty($relays)) {
            Message::info("No relays found!");
            return 0;
        }

        $rows = [];
        foreach ($relays as $name => $class) {
            $rows[] = [$name, $class];
        }

        Table::render('RELAY CLASSES', ['# RELAY NAME', '# RELAY CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika relay:list";
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
