<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class ControllerRemoveCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'remove:controller';
    }

    public function description(): string
    {
        return 'Remove a controller class';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Old & New Controller Names
        $name = $args[0];

        // Validate Old & New Scontroller Name
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Controller name should contain characters only!");
            return 1;
        }

        // Confirm From User
        if (!Argument::readline("Confirm remove [App\\Controller\\{$name}]?")) {
            Message::warning("Canceled by user!", 'console');
            return 0;
        }

        // Get Path
        $path = "{$basePath}/lf-app/Controller/{$name}.php";

        // Check Controller Exists
        if (!is_file($path)) {
            Message::error("Controller [{$name}] doesn't exists!");
            return 1;
        }

        try {
            // Get Controller Content
            if (!unlink($path)) {
                 Message::error("Controller [{$name}] remove failed.");
                 return 1;
            }
            Message::success("Controller [{$name}] removed successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika remove:controller <name>";
    }

    public function help(): string
    {
        return <<<HELP
        CONTROLLER MAKE COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :
                name    :   Controller name

            PARAMETERS  :   No parameters available

        HELP;
    }
}
