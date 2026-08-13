<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Service\Infra;

class TemplateListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'template:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $templates = Infra::getTemplateNames();

        if (empty($templates)) {
            Message::info("No templates found!");
            return 0;
        }

        $rows = [];
        foreach ($templates as $path => $sets) {
            foreach ($sets as $set) {
                foreach ($set as $ext => $name) {
                    $rows[] = [$path, $name, $ext];
                }
            }
        }

        Table::render('TEMPLATES', ['# PATH', '# TEMPLATE NAME', '# EXTENSION'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika template:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of template files',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
