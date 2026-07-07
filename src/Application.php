<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Cli\Command\CommandInterface;
use Laika\Cli\Command\AfterwareListCommand;
use Laika\Cli\Command\MakeRouteCommand;
use Laika\Cli\Command\MakeMiddlewareCommand;
use Laika\Cli\Command\MakeAfterwareCommand;
use Laika\Cli\Command\MakeModelCommand;
use Laika\Cli\Command\MakeSchemaCommand;
use Laika\Cli\Command\MakeTemplateCommand;
use Laika\Cli\Command\MakeServiceCommand;
use Laika\Cli\Command\MakeControllerCommand;
// use Laika\Cli\Command\ListCommand;
use Laika\Cli\Command\ListControllerCommand;
use Laika\Cli\Command\ListMiddlewareCommand;
use Laika\Cli\Command\ListModelCommand;
use Laika\Cli\Command\ListRouteCommand;
use Laika\Cli\Command\ListSchemaCommand;
use Laika\Cli\Command\ListRelayCommand;
use Laika\Cli\Command\ListTemplateCommand;
// use Laika\Cli\Command\RemoveCommand;
// use Laika\Cli\Command\RenameCommand;
use Laika\Cli\Command\RenameRouteCommand;
use Laika\Cli\Command\SecretFixCommand;
use Laika\Cli\Command\SecretGenerateCommand;

class Application
{
    /** @var CommandInterface[] */
    protected array $commands = [];

    public function __construct(protected string $basePath)
    {
        // Service
        $this->register(new ListRelayCommand);
        $this->register(new MakeServiceCommand());

        // Model
        $this->register(new ListModelCommand());
        $this->register(new MakeModelCommand());
        
        // Schema
        $this->register(new ListSchemaCommand);
        $this->register(new MakeSchemaCommand());

        // Middleware
        $this->register(new ListMiddlewareCommand);
        $this->register(new MakeMiddlewareCommand());

        // Afterware
        $this->register(new AfterwareListCommand);
        $this->register(new MakeAfterwareCommand());

        // Controller
        $this->register(new ListControllerCommand);
        $this->register(new MakeControllerCommand());

        // Template
        $this->register(new ListTemplateCommand);
        $this->register(new MakeTemplateCommand());

        // Route
        $this->register(new ListRouteCommand());
        $this->register(new MakeRouteCommand());

        // Secret
        $this->register(new SecretGenerateCommand());
        $this->register(new SecretFixCommand());
    }

    protected function register(CommandInterface $command): void
    {
        $this->commands[$command->signature()] = $command;
    }

    public function run(array $argv): int
    {
        array_shift($argv);
        $signature = $argv[0] ?? null;

        if (!$signature || !isset($this->commands[$signature])) {
            $this->help();
            return 1;
        }

        $args = array_slice($argv, 1);

        try {
            return $this->commands[$signature]->handle($args, $this->basePath);
        } catch (\Throwable $e) {
            fwrite(STDERR, "Error: {$e->getMessage()}\n");
            return 1;
        }
    }

    protected function help(): void
    {
        echo "Laika CLI\n\nAvailable commands:\n";
        foreach ($this->commands as $signature => $command) {
            printf("  %-20s %s\n", $signature, $command->description());
        }
    }
}
