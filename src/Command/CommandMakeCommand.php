<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;
use Laika\Service\Directory;
use Laika\Cli\Contracts\CommandInterface;

class CommandMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'command:make';
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(1, 3))) {
            Message::suggestion($this->command());
            return 1;
        }

        // Get Command Class Name & Validate
        $name = $args[0];
        if (empty($name)) {
            Message::error("Command name should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $name)) {
            Message::error("Invalid command name [{$name}].");
            return 1;
        }

        // The file name has to match the class for resource discovery to map it
        $class = $this->className($name);

        // Get Signature & Validate
        $signature = Argument::getValue('signature', $args, $this->defaultSignature($class));
        if (!preg_match('/^[a-z][a-z0-9_-]*:[a-z][a-z0-9_-]*$/', (string) $signature)) {
            Message::error("Invalid signature [{$signature}]. Expected <resource>:<action>, all lowercase.");
            return 1;
        }

        $commandDir = $basePath . DS . 'lf-app' . DS . 'Command';
        // Make Directory if Doesn't Exists
        Directory::make($commandDir);
        $path = $commandDir . DS . "{$class}.php";

        // Check Command Doesn't Exists
        if (is_file($path)) {
            Message::error("Command [{$class}] already exists.");
            return 1;
        }

        try {
            $content = Stub::render('command', [
                'class'         =>  $class,
                'signature'     =>  (string) $signature,
                'description'   =>  Argument::getValue('description', $args, "Run {$signature}"),
            ]);

            Stub::write($path, $content);
            Message::success("Command [{$class}] created successfully. Run it with: php laika {$signature}");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    /**
     * Normalise to a Command Suffixed Class Name. Monitor => MonitorCommand
     * @param string $name
     * @return string
     */
    protected function className(string $name): string
    {
        $name = ucfirst($name);

        return str_ends_with($name, 'Command') ? $name : "{$name}Command";
    }

    /**
     * Derive a Signature From The Class Name. MonitorCommand => monitor:run
     * @param string $class
     * @return string
     */
    protected function defaultSignature(string $class): string
    {
        $base = preg_replace('/Command$/', '', $class);
        $base = strtolower(trim((string) preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $base), '-'));

        return ($base === '' ? 'app' : $base) . ':run';
    }

    public function command(): string
    {
        return "php laika command:make <name> [--signature=name:run] [--description=...]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new app command in lf-app/Command',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Command class name'],
            'params'        =>  [
                                    'signature'     =>  'How the command is invoked. Example: monitor:check',
                                    'description'   =>  'Text shown in php laika help',
                                ]
        ];
    }
}
