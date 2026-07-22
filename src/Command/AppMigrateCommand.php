<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;
use Laika\Model\Schema\Schema;

class AppMigrateCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'app:migrate';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Single Schema if Exists
        $table = Argument::getValue('table', $args);
        $tables = Infra::getSchemaClasses();

        if ($table) {
            if (array_key_exists($table, $tables)) {
                $tables = [$table => $tables[$table]];
            } else {
                Message::error("Schema for table [{$table}] doesn't exists!");
                $matches = Argument::checkMatch($table, array_keys($tables));

                if (!empty($matches)) {
                    echo "You May Trying To Migrate:\n";
                    foreach ($matches as $m) {
                        echo "\t{$m}\n";
                    }
                }
                return 1;
            }
        }

        // Create Table
        $list = [];
        try {
            // Migrate Schema
            foreach ($tables as $t => $s) {
                $obj = new $s;
                $sObj = Schema::on($obj->connection);
                $sObj->disableForeignKeyChecks();
                $obj->up();
                $obj->seed();
                $sObj->enableForeignKeyChecks();
                $list[] = $t;
            }
            // Success Message
            Message::success("Migrated Database Table(s):");
            echo "----------------------------\n";
            foreach ($list as $t) {
                echo "\t{$t}\n";
            }
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika app:migrate [--table=name]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Migrate app Database and Additionals.',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['table' => 'Table name']
        ];
    }
}
