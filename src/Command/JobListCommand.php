<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Table;
use Laika\Service\Infra;

class JobListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'job:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $jobs = Infra::getQueueJobsClasses();

        if (empty($jobs)) {
            Message::info("No jobs found!");
            return 0;
        }

        $rows = [];
        foreach ($jobs as $j) {
            $rows[] = [count($rows) + 1, $j];
        }

        Table::render('JOB CLASSES', ['# SL', '# JOB CLASS'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika job:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered job classes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
