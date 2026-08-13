<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Service\Infra;

class ModelListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'model:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $models = Infra::getModelClasses();

        if (empty($models)) {
            Message::info("No models found!");
            return 0;
        }

        $rows = [];
        foreach ($models as $t => $c) {
            $rows[] = [$t, $c];
        }

        Table::render('MODEL CLASSES', ['# TABLE NAME', '# MODEL CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika model:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered model cLasses',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
