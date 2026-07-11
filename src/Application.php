<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Cli\Command\CommandInterface;
use Laika\Cli\Command\AfterwareListCommand;
use Laika\Cli\Command\AppSyncCommand;
use Laika\Cli\Command\RouteListCommand;
use Laika\Cli\Command\RouteMakeCommand;
use Laika\Cli\Command\PipelineMakeCommand;
use Laika\Cli\Command\PipelineRenameCommand;
use Laika\Cli\Command\MakeAfterwareCommand;
use Laika\Cli\Command\ModelMakeCommand;
use Laika\Cli\Command\MakeSchemaCommand;
use Laika\Cli\Command\MakeTemplateCommand;
use Laika\Cli\Command\ServiceMakeCommand;
use Laika\Cli\Command\MakeControllerCommand;
use Laika\Cli\Command\ListControllerCommand;
use Laika\Cli\Command\PipelineListCommand;
use Laika\Cli\Command\ModelListCommand;
use Laika\Cli\Command\ListSchemaCommand;
use Laika\Cli\Command\RelayListCommand;
use Laika\Cli\Command\ListTemplateCommand;
use Laika\Cli\Command\PipelineRemoveCommand;
use Laika\Cli\Command\RenameRouteCommand;
use Laika\Cli\Command\SecretFixCommand;
use Laika\Cli\Command\SecretGenerateCommand;
use Laika\Cli\Command\ServiceRemoveCommand;

class Application
{
    /** @var CommandInterface[] */
    protected array $commands = [];

    public function __construct(protected string $basePath)
    {
        // Service
        $this->register(new RelayListCommand); // Done
        $this->register(new ServiceMakeCommand()); // Done
        $this->register(new ServiceRemoveCommand()); // Done

        // Model
        $this->register(new ModelListCommand()); // Done
        $this->register(new ModelMakeCommand()); // Done
        
        // Schema
        $this->register(new ListSchemaCommand);
        $this->register(new MakeSchemaCommand());

        // Pipeline
        $this->register(new PipelineListCommand); // Done
        $this->register(new PipelineMakeCommand()); // Done
        $this->register(new PipelineRenameCommand()); // Done
        $this->register(new PipelineRemoveCommand()); // Done

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
        $this->register(new RouteListCommand()); // Done
        $this->register(new RouteMakeCommand());

        // Secret
        $this->register(new SecretGenerateCommand()); // Done
        $this->register(new SecretFixCommand()); // Done

        // Sync
        $this->register(new AppSyncCommand()); // Done
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
            Command\Message::error($e->getMessage());
            return 1;
        }
    }

    protected function help(): void
    {
        echo "\n" . Command\Message::txt_yellow(' LAIKA CLI AVAILABLE COMMANDS ') . "\n";
        echo "------------------------------------------------------------------------------------\n";
        foreach ($this->commands as $signature => $command) {
            printf("  %s\n", $command->help());
            echo "------------------------------------------------------------------------------------\n";
        }
    }
}
