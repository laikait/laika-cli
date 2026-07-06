<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Cli\Command\CommandInterface;
use Laika\Cli\Command\ListAfterwareCommand;
use Laika\Cli\Command\MakeRouteCommand;
use Laika\Cli\Command\MakeMiddlewareCommand;
use Laika\Cli\Command\MakeAfterwareCommand;
use Laika\Cli\Command\MakeModelCommand;
use Laika\Cli\Command\MakeSchemaCommand;
use Laika\Cli\Command\MakeTemplateCommand;
use Laika\Cli\Command\MakeServiceCommand;
use Laika\Cli\Command\MakeControllerCommand;
use Laika\Cli\Command\ListCommand;
use Laika\Cli\Command\ListControllerCommand;
use Laika\Cli\Command\ListMiddlewareCommand;
use Laika\Cli\Command\ListModelCommand;
use Laika\Cli\Command\ListRouteCommand;
use Laika\Cli\Command\ListSchemaCommand;
use Laika\Cli\Command\ListRelayCommand;
use Laika\Cli\Command\ListTemplaeCommand;
use Laika\Cli\Command\RemoveCommand;
use Laika\Cli\Command\RenameCommand;
use Laika\Cli\Command\RenameRouteCommand;

class Application
{
    /** @var CommandInterface[] */
    protected array $commands = [];

    public function __construct(protected string $basePath)
    {
        // Make
        $this->register(new MakeRouteCommand());
        $this->register(new MakeMiddlewareCommand());
        $this->register(new MakeAfterwareCommand());
        $this->register(new MakeModelCommand());
        $this->register(new MakeSchemaCommand());
        $this->register(new MakeTemplateCommand());
        $this->register(new MakeServiceCommand());
        $this->register(new MakeControllerCommand());

        // List
        $this->register(new ListRouteCommand());
        $this->register(new ListModelCommand());
        $this->register(new ListSchemaCommand);
        $this->register(new ListControllerCommand);
        $this->register(new ListMiddlewareCommand);
        $this->register(new ListAfterwareCommand);
        $this->register(new ListTemplaeCommand);
        $this->register(new ListRelayCommand);

        // Remove
        $this->register(new RemoveCommand('route', 'lf-routes'));
        $this->register(new RemoveCommand('middleware', 'app/Middleware'));
        $this->register(new RemoveCommand('afterware', 'app/Afterware'));
        $this->register(new RemoveCommand('model', 'app/Model'));
        $this->register(new RemoveCommand('service', 'app/Service'));
        $this->register(new RemoveCommand('controller', 'app/Controller'));

        $this->register(new RenameCommand('middleware', 'app/Middleware', 'App\\Middleware'));
        $this->register(new RenameCommand('afterware', 'app/Afterware', 'App\\Afterware'));
        $this->register(new RenameCommand('model', 'app/Model', 'App\\Model'));
        $this->register(new RenameCommand('service', 'app/Service', 'App\\Service'));
        $this->register(new RenameCommand('controller', 'app/Controller', 'App\\Controller'));

        $this->register(new RenameRouteCommand());
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
