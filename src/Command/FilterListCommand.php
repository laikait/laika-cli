<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Service\Infra;

class FilterListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'filter:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $filters = Infra::getFilterClasses();

        if (empty($filters)) {
            Message::info("No filters found!");
            return 0;
        }

        $rows = [];
        foreach ($filters as $f) {
            $rows[] = [count($rows) + 1, $f];
        }

        Table::render('FILTER CLASSES', ['# SL', '# FILTER CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika filter:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered filter classes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
