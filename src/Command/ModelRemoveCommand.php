<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class ModelRemoveCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'model:remove';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Model Name & Validate
        $name = $args[0];
        if (empty($name)) {
            Message::error("Model name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid model name [{$name}].");
            return 1;
        }

        $model_path = $basePath . "/lf-app/Model/{$name}.php";
        $schema_path = $basePath . "/lf-app/Schema/{$name}Schema.php";

        if (!is_file($model_path)) {
            Message::error("Model [{$name}] not found!");
            return 1;
        }

        try {
            if (!unlink($model_path)) {
                Message::error("Model [{$name}] remove failed!");
                return 1;
            }

            if (is_file($schema_path)) {
                if (!unlink($schema_path)) {
                    Message::error("Schema [{$name}Schema] remove failed!");
                    return 1;
                }
                Message::success("Model [{$name}] & Schema [{$name}Schema] removed successfully.");
            } else {
                Message::success("Model [{$name}] removed & no schema found.");
            }
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika model:remove <name>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Remove a model class',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Model name to remove'],
            'params'        =>  []
        ];
    }
}
