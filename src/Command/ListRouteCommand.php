<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Route\Handler;
use Laika\Route\Url;

class ListRouteCommand implements CommandInterface
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
        $dir = $basePath . '/lf-routes';

        if (!is_dir($dir)) {
            echo "No routes found.\n";
            return 0;
        }

        // Load Routes
        Url::loadRoutes($dir);

        // Get Method
        $method = null;

        foreach (array_slice($args, 1) as $arg) {
            if (str_starts_with($arg, '--method=')) {
                $method = substr($arg, 9);
            }
        }
        $routes = $method ? [strtoupper($method) => Handler::getOnlyRoutes($method)] : Handler::getRoutes();
        $total = 0;

        $head = sprintf("%-8s | %-30s | %-40s | %-40s\n", '# METHOD', '# URI', '# CONTROLLER', '# NAME');
        echo str_repeat('-', strlen($head)) . "\n";
        echo $head;
        echo str_repeat('-', strlen($head)) . "\n";

        foreach ($routes as $method => $uris) {
            foreach ($uris as $uri => $data) {
                $controller = is_string($data['controller'])
                    ? $data['controller']
                    : (is_array($data['controller']) ? implode('@', $data['controller']) : 'Closure');

                printf("%-8s | %-30s | %-40s | %-40s\n", $method, $uri, $controller, $data['name']);
                // echo str_repeat('-', strlen($head)) . "\n";
                $total++;
            }
        }
        echo str_repeat('-', strlen($head)) . "\n";
        echo "Total: {$total}\n";

        return 0;
    }
}
