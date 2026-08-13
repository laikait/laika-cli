<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Table;
use Laika\Route\Path;
use Laika\Route\Handler;
use Laika\Service\Infra;

class RouteListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'route:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $dir = $basePath . '/lf-routes';

        // Load Routes
        // Path::loadRoutes($dir);
        foreach (Infra::getRouteFiles() as $rf) require_once $rf;

        // Get Method
        $method = Argument::getValue('method', $args);

        $routes = $method ? [strtoupper($method) => Handler::getOnlyRoutes($method)] : Handler::getRoutes();

        // Check Route Exists
        if (empty($routes)) {
            Message::info('No routes found!');
            return 0;
        }

        $rows = [];
        foreach ($routes as $method => $uris) {
            foreach ($uris as $uri => $data) {
                $controller = is_string($data['controller'])
                    ? $data['controller']
                    : (is_array($data['controller']) ? implode('@', $data['controller']) : 'Closure');

                $rows[] = [$method, $uri, $controller, $data['name'] ?? '----'];
            }
        }

        Table::render('ROUTES', ['# METHOD', '# URI', '# RESPONSE', '# NAME'], $rows);

        return 0;
    }

    public function command(): string
    {
        return "php laika route:list [--method=get]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List of registered routes',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['method' => 'Get routes by method.']
        ];
    }
}
