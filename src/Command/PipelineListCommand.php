<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Service\Infra;

class PipelineListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'pipeline:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $pipelines = Infra::getPipelineClasses();

        if (empty($pipelines)) {
            Message::info("No pipelines found!");
            return 0;
        }

        $rows = [];
        foreach ($pipelines as $pipeline) {
            $rows[] = [count($rows) + 1, $pipeline];
        }

        Table::render('PIPELINE CLASSES', ['# SL', '# PIPELINE CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika pipeline:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered pipeline classes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
