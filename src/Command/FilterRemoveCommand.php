<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class FilterRemoveCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'remove:filter';
    }

    public function description(): string
    {
        return 'Remove a filter class';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Filter Name & Validate
        $name = $args[0];
        if (empty($name)) {
            Message::error("Filter name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid filter name [{$name}].");
            return 1;
        }

        $path = "{$basePath}/lf-app/Filter/{$name}.php";

        if (!is_file($path)) {
            Message::error("Filter [{$name}] not found!");
            return 1;
        }

        try {
            // Confirm
            $action = Argument::readline('Continue?');
            if (!$action) {
                echo "Filter [{$name}] remove canceled!\n";
                return 0;
            }
            if (!unlink($path)) {
                Message::error("Filter [{$name}] remove failed!");
                return 1;
            }

            Message::success("Filter [{$name}] removed successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika remove:filter <name>";
    }

    public function help(): string
    {
        return <<<HELP
        FILTER MAKE COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :
                name    :   Filter name to remove

            PARAMETERS  :   No parameters available

        HELP;
    }
}
