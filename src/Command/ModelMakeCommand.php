<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class ModelMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'model:make';
    }

    public function description(): string
    {
        return 'Create a new model class';
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(1,4))) {
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

        // Get Table Name & Validate
        $table = Argument::getValue('--table', $args, strtolower($name));
        if (empty($table)) {
            Message::error("Table name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $table)) {
            Message::error("Invalid table name [{$table}].");
            return 1;
        }

        // Get Primary Column Name & Validate
        $id = Argument::getValue('--id', $args, 'id');
        if (empty($id)) {
            Message::error("Table primary column name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $id)) {
            Message::error("Invalid primary column name [{$id}].");
            return 1;
        }

        // Get UID Column Name & Validate
        $uid = Argument::getValue('--uid', $args, 'uid');
        if (empty($uid)) {
            Message::error("Table uid column name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $uid)) {
            Message::error("Invalid uid column name [{$uid}].");
            return 1;
        }

        // Get Database Connection Name & Validate
        $con = Argument::getValue('--connection', $args, 'default');
        if (empty($con)) {
            Message::error("Database connection name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $con)) {
            Message::error("Invalid Database connection name [{$con}].");
            return 1;
        }

        $model_path = $basePath . "/lf-app/Model/{$name}.php";

        try {
            $model_content = Stub::render('model', [
                'class' =>  $name,
                'table' =>  $table,
                'id'    =>  $id,
                'uid'   =>  $uid,
                'con'   =>  $con
            ]);

            Stub::write($model_path, $model_content);

            $schema_class = "{$name}Schema";
            $schema_path = $basePath . "/lf-app/Schema/{$schema_class}.php";

            if (is_file($schema_path)) {
                Message::error("Schema [{$schema_class}] already exists!");
                return 1;
            }

            $schema_content = Stub::render('schema', [
                'model' =>  $name,
                'class' =>  $schema_class,
                'table' =>  $table,
                'id'    =>  $id,
                'uid'   =>  $uid,
                'con'   =>  $con
            ]);

            Stub::write($schema_path, $schema_content);

            Message::success("Model [{$name}] created successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika model:make <name> [--table=table] [--id=id] [--uid=uid] [--connection=name]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new model class',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [
                                    'table'     =>  'Table name',
                                    'id'        =>  'Primary column name',
                                    'uid'       =>  'Unique ID column name',
                                    'connection'=>  'Database connection name'
                                ]
        ];
    }
}
