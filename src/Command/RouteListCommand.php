<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Route\Url;
use Laika\Route\Handler;

class RouteListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'list:route';
    }

    public function description(): string
    {
        return 'List Registered Routes';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $dir = $basePath . '/lf-routes';

        // Load Routes
        Url::loadRoutes($dir);

        // Get Method
        $method = Argument::getValue('--method', $args);

        $routes = $method ? [strtoupper($method) => Handler::getOnlyRoutes($method)] : Handler::getRoutes();

        // Check Route Exists
        if (empty($routes)) {
            Message::info('No Routes Found.');
            return 0;
        }

        $total = 0;

        $head = sprintf("%-8s | %-30s | %-40s | %-40s\n", '# METHOD', '# URI', '# RESPONSE', '# NAME');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach ($routes as $method => $uris) {
            foreach ($uris as $uri => $data) {
                $response = is_string($data['response'])
                    ? $data['response']
                    : (is_array($data['response']) ? implode('@', $data['response']) : 'Closure');

                printf("%-8s | %-30s | %-40s | %-40s\n", $method, $uri, $response, $data['name'] ?? '----');
                $total++;
            }
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }

    public function command(): string
    {
        return "php laika list:route";
    }

    public function help(): string
    {
        return <<<HELP
        SECRET GENERATE COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :   No inputs Available

            PARAMETERS  :   No parameters available

        HELP;
    }
}
