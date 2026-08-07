<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;
use Laika\Service\Infra;

class ServiceRemoveCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'service:remove';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Name & Validate Name
        $name = ucfirst($args[0]);
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid name [{$name}]");
            return 1;
        }

        $service_path = "{$basePath}/lf-app/Service/{$name}.php";
        $relay_path = "{$basePath}/lf-app/Relay/{$name}.php";

        // Check Service Class Exists
        if (!is_file($service_path)) {
            Message::error("App service [{$name}] doesn't exists.");
            return 1;
        }

        // Check App Relay Class Exists
        if (!is_file($relay_path)) {
            Message::error("App relay [{$name}] doesn't exists.");
            return 1;
        }

        try {
            unlink($service_path);
            unlink($relay_path);
            Message::success("Service [{$name}] removed successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika service:remove <name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Remove a service class',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Service name to remove'],
            'params'        =>  []
        ];
    }
}
