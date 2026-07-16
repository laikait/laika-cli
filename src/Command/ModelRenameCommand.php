<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class ModelRenameCommand implements CommandInterface
{
    public function signature(): string
    {
        return "rename:model";
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(2, 5))) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Old & New Model Names
        $old = Argument::getValue('--old', $args);
        $new = Argument::getValue('--new', $args);

        // Validate Old & New Model Name
        if (empty($old) || empty($new)) {
            Message::error("Old/New name should not be empty!");
            return 1;
        }

        if (!preg_match('/^[a-z_]+$/i', $old) || !preg_match('/^[a-z_]+$/i', $new)) {
            Message::error("Model name should contain characters only!");
            return 1;
        }

        $oldModelPath = $basePath . "/lf-app/Model/{$old}.php";
        $newModelPath = $basePath . "/lf-app/Model/{$new}.php";

        $oldSchemaPath = $basePath . "/lf-app/Schema/{$old}Schema.php";
        $newSchemaPath = $basePath . "/lf-app/Schema/{$new}Schema.php";

        // Check Old Model Exists
        if (!is_file($oldModelPath)) {
            Message::error("Model [{$old}] doesn't exists!");
            return 1;
        }

        // Check New Model Doesn't Exists
        if (is_file($newModelPath)) {
            Message::error("Model [{$new}] already exists!");
            return 1;
        }

        try {
            // Get Table Name, ID & UID Column Names
            $class = "\\App\\Model\\{$old}";
            $reflection = new \ReflectionClass($class);
            $obj = $reflection->newInstanceWithoutConstructor();
            $table = strtolower(Argument::getValue('--table', $args, $obj->table));
            $id = strtolower(Argument::getValue('--id', $args, $obj->id));
            $uid = strtolower(Argument::getValue('--uid', $args, $obj->uid));

            // Get Model Content
            $content = file_get_contents($oldModelPath);

            $patterns = [
                '/\bclass\s+' . preg_quote($old, '/') . '\b/i',
                '/protected\s+string\s+\$table\s*=\s*[\'"]' . preg_quote($obj->table, '/') . '[\'"]\s*;/i',
                '/protected\s+string\s+\$id\s*=\s*[\'"]' . preg_quote($obj->id, '/') . '[\'"]\s*;/i',
                '/protected\s+string\s+\$uid\s*=\s*[\'"]' . preg_quote($obj->uid, '/') . '[\'"]\s*;/i'
            ];

            $replacements = [
                "class {$new}",
                "protected string \$table = '{$table}';",
                "protected string \$id = '{$id}';",
                "protected string \$uid = '{$uid}';"
            ];

            $content = preg_replace($patterns, $replacements, $content);

            rename($oldModelPath, $newModelPath);
            file_put_contents($newModelPath, $content);

            // Rename Schema if Exists
            if (is_file($oldSchemaPath)) {
                $content = file_get_contents($oldSchemaPath);

                $patterns = [
                    '/use\s+App\\\\Model\\\\' . preg_quote($old, '/') . '\s*;/i',
                    '/\bclass\s+' . preg_quote($old, '/') . 'Schema\b/i',
                    '/protected\s+string\s+\$table\s*=\s*[\'"]' . preg_quote($obj->table, '/') . '[\'"]\s*;/i',
                    '/new\s*' . preg_quote($old, '/') . '\(\)/i',
                    '/\bfunction\s*\(\s*' . preg_quote($old, '/') . '\b/i',
                ];

                $replacements = [
                    "use App\\Model\\{$new};",
                    "class {$new}Schema",
                    "protected string \$table = '{$table}';",
                    "new {$new}()",
                    "function ({$new}"
                ];

                $content = preg_replace($patterns, $replacements, $content);

                rename($oldSchemaPath, $newSchemaPath);
                file_put_contents($newSchemaPath, $content);
                Message::success("Renamed model [{$old}] -> [{$new}] & Schema [{$old}Schema] -> [{$new}Schema].");
            } else {
                Message::success("Renamed model [{$old}] -> [{$new}]");
            }
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika rename:model <--old=name> <--new=name> [--table=table] [--id=id] [--uid=uid]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Rename model class, table, primary & UID column name',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Model name to remove'],
            'params'        =>  [
                                    'old'   =>  '(Required) old model name',
                                    'new'   =>  '(Required) new model name',
                                    'table' =>  '(Optional) Model table name',
                                    'id'    =>  '(Optional) Model primary column name',
                                    'uid'   =>  '(Optional) Model UID column name'
                                ]
        ];
    }
}
