<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Cli\Stub;

class ControllerMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'controller:make';
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(1,2))) {
            Message::suggestion($this->command());
            return 1;
        }

        $name = $args[0];
        $method = Argument::getValue('method', $args, 'index');

        // Validate Controller Name & Method
        $name = $args[0];
        if (empty($name)) {
            Message::error("Controller name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid controller name [{$name}].");
            return 1;
        }
        if (empty($method)) {
            Message::error("Method name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid method name [{$name}].");
            return 1;
        }

        $path = "{$basePath}/lf-app/Controller/{$name}.php";

        if (is_file($path)) {
            $class = "App\\Controller\\{$name}";
            if (method_exists($class, $method)) {
                Message::error("You are trying to add [{$class}::{$method}()]. Already exists!");
                return 1;
            }

            // Append Method
            try {
                $oldContent = file_get_contents($path);
                $position = strrpos($oldContent, '}');
                $stubContent = Stub::render('controller-method', [
                    'method'    =>  $method,
                ]);

                $content = substr_replace($oldContent, $stubContent . '}', $position, 1);
                Stub::write($path, $content);
                Message::success("Controller [{$name}] created successfully!");
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
                return 1;
            }
            return 0;
        }

        try {
            $content = Stub::render('controller', [
                'class'     =>  $name,
                'method'    =>  $method,
            ]);

            Stub::write($path, $content);
            Message::success("Controller [{$name}] created successfully!");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika controller:make <name> [--method=index]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new controller ',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Controller class name'],
            'params'        =>  ['method' => 'Controller method name']
        ];
    }
}
