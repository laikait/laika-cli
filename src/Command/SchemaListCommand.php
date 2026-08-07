<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Table;
use Laika\Service\Infra;

class SchemaListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'schema:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $schemas = Infra::getSchemaClasses();

        if (empty($schemas)) {
            Message::info("No schemas found!");
            return 0;
        }

        $rows = [];
        foreach ($schemas as $t => $c) {
            $rows[] = [$t, $c];
        }

        Table::render('SCHEMA CLASSES', ['# TABLE NAME', '# SCHEMA CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika schema:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered schema classes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
