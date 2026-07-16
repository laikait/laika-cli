<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class FilterMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:filter';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Validate Filter Name
        $name = $args[0];
        if (!preg_match('/[a-z_]/i', $name)) {
            Message::error("Invalid filter name: [{$name}]!");
            return 1;
        }

        $path = $basePath . "/lf-app/Filter/{$name}.php";

        // Check Filter doesn't exists
        if (is_file($path)) {
            Message::error("Filter [{$name}] already exists.");
            return 1;
        }

        try {
            $content = Stub::render('filter', [
                'class' => $name,
            ]);

            Stub::write($path, $content);
            Message::success("Filter [{$name}] created successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika make:filter <name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new filter class',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' =>  'Filter class name'],
            'params'        =>  []
        ];
    }
}
