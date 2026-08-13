<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Stub;

class RouteMakeCommand implements CommandInterface
{
    protected const METHODS = ['get', 'post', 'put', 'patch', 'delete', 'options'];

    public function signature(): string
    {
        return 'route:make';
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(1, 3))) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Route Name & Validate
        $name = strtolower($args[0] ?? '');
        if (empty($name)) {
            Message::error("Route name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid route name [{$name}].");
            return 1;
        }

        // Get HTTP Method & Validate
        $method = strtolower(Argument::getValue('method', $args, 'get'));
        if (!in_array($method, self::METHODS, true)) {
            Message::error("Invalid method [{$method}]. Accepted: " . implode(', ', self::METHODS));
            return 1;
        }

        // Get Target Routes File & Validate — appends to it if it already
        // exists, or creates it fresh if this is the first route going
        // there. Defaults to web.php, same as a fresh Laika project ships.
        $file = strtolower(Argument::getValue('file', $args, 'web'));
        if (!preg_match('/^[a-z_]+$/i', $file)) {
            Message::error("Invalid file name [{$file}]. Example: web, api, admin, etc. (without .php extension)");
            return 1;
        }

        $path = "{$basePath}/lf-routes/{$file}.php";
        $uri = $name;
        $controller = ucfirst($name) . 'Controller';

        // Route ->name() has to be unique across the whole app regardless
        // of HTTP method — Handler::name() keys purely by name, not
        // method+name — so a second verb on the same URI (post /products
        // alongside get /products) needs a name of its own, or it collides
        // with the first route's ->name() at route-load time. 'get' keeps
        // the bare name since that's the common case and matches existing
        // convention (->name('home'), no suffix).
        $routeName = $method === 'get' ? $name : "{$name}.{$method}";

        try {
            $isNewFile = !is_file($path);
            if ($isNewFile) {
                Stub::write($path, Stub::load('route'));
            }

            $content = file_get_contents($path);

            // Already registered? Same method + URI in this file — don't
            // duplicate it.
            if (preg_match('/Url::' . preg_quote($method, '/') . '\s*\(\s*[\'"]\/' . preg_quote($uri, '/') . '[\'"]/', $content)) {
                Message::error("Route [{$method} /{$uri}] already exists in lf-routes/{$file}.php.");
                return 1;
            }

            $entry = Stub::render('route-entry', [
                'method'        =>  $method,
                'uri'           =>  $uri,
                'controller'    =>  $controller,
                'name'          =>  $routeName,
            ]);

            // A blank line only between the boilerplate and the first
            // route in a file this command just created — appending a
            // second route onto an already-populated file sits flush
            // under the one before it, same as if you'd typed it by hand.
            $separator = $isNewFile ? "\n\n" : "\n";
            file_put_contents($path, rtrim($content) . $separator . $entry);

            Message::success("Route [{$method} /{$uri}] added to lf-routes/{$file}.php.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika route:make <name> [--method=get] [--file=web]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Add a new route, appending it to a routes file (default: web.php)',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Route name — used as the URI, controller prefix, and route name'],
            'params'        =>  [
                                    'method'    =>  'HTTP method: get, post, put, patch, delete, options (default: get)',
                                    'file'      =>  'Target file under lf-routes/, without extension (default: web)',
                                ]
        ];
    }
}
