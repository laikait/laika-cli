<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;

class PipelineListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:pipeline';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 0) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Pipelines
        $pipelines = Infra::getPipelineClasses();

        // Check Pipeline Exists
        if (empty($pipelines)) {
            Message::info("No Pipelines Foind.");
            return 1;
        }

        $total = 0;

        $head = sprintf("%-4s | %-50s\n", '# SL', '# PIPELINE CLASS');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach ($pipelines as $pipeline) {
            $total++;
            printf("%-4s | %-50s\n", $total, $pipeline);
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }

    public function command(): string
    {
        return "php laika list:pipeline";
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
