<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Service\Infra;
use Laika\Cli\Command\Message;
use Laika\Cli\Command\Argument;
use Laika\Cli\Command\HelpCommand;
use Laika\Cli\Command\AppSyncCommand;
use Laika\Cli\Command\RouteListCommand;
use Laika\Cli\Command\ModelListCommand;
use Laika\Cli\Command\ModelMakeCommand;
use Laika\Cli\Command\CommandInterface;
use Laika\Cli\Command\SecretFixCommand;
use Laika\Cli\Command\RelayListCommand;
use Laika\Cli\Command\SchemaListCommand;
use Laika\Cli\Command\FilterListCommand;
use Laika\Cli\Command\FilterMakeCommand;
use Laika\Cli\Command\AppMigrateCommand;
use Laika\Cli\Command\ModelRemoveCommand;
use Laika\Cli\Command\ServiceMakeCommand;
use Laika\Cli\Command\ModelRenameCommand;
use Laika\Cli\Command\PipelineListCommand;
use Laika\Cli\Command\TemplateListCommand;
use Laika\Cli\Command\PipelineMakeCommand;
use Laika\Cli\Command\FilterRemoveCommand;
use Laika\Cli\Command\FilterRenameCommand;
use Laika\Cli\Command\TemplateMakeCommand;
use Laika\Cli\Command\ServiceRemoveCommand;
use Laika\Cli\Command\PipelineRenameCommand;
use Laika\Cli\Command\ControllerMakeCommand;
use Laika\Cli\Command\ControllerListCommand;
use Laika\Cli\Command\SecretGenerateCommand;
use Laika\Cli\Command\PipelineRemoveCommand;
use Laika\Cli\Command\ControllerRemoveCommand;
use Laika\Cli\Command\ControllerRenameCommand;

foreach (Infra::getFunctionFiles() as $file) require_once $file;

class Application
{
    /** @var CommandInterface[] */
    protected array $commands = [];

    public function __construct(protected string $basePath)
    {
        // Help
        $this->register(new HelpCommand());
        // Service
        $this->register(new RelayListCommand);
        $this->register(new ServiceMakeCommand());
        $this->register(new ServiceRemoveCommand());

        // Model
        $this->register(new ModelListCommand());
        $this->register(new ModelMakeCommand());
        $this->register(new ModelRemoveCommand());
        $this->register(new ModelRenameCommand());
        $this->register(new SchemaListCommand);

        // Pipeline
        $this->register(new PipelineListCommand);
        $this->register(new PipelineMakeCommand());
        $this->register(new PipelineRenameCommand());
        $this->register(new PipelineRemoveCommand());

        // Filter
        $this->register(new FilterListCommand);
        $this->register(new FilterMakeCommand());
        $this->register(new FilterRenameCommand());
        $this->register(new FilterRemoveCommand());

        // Controller
        $this->register(new ControllerListCommand);
        $this->register(new ControllerMakeCommand());
        $this->register(new ControllerRenameCommand());
        $this->register(new ControllerRemoveCommand());

        // Template
        $this->register(new TemplateListCommand);
        $this->register(new TemplateMakeCommand());

        // Route
        $this->register(new RouteListCommand());

        // Secret
        $this->register(new SecretGenerateCommand());
        $this->register(new SecretFixCommand());

        // App
        $this->register(new AppSyncCommand());
        $this->register(new AppMigrateCommand());
    }

    protected function register(CommandInterface $command): void
    {
        $this->commands[$command->signature()] = $command;
    }

    ######################################################################################
    /*################################## EXTERNAL API ##################################*/
    ######################################################################################
    public function run(array $argv): int
    {
        $command = implode(' ', $argv);
        array_shift($argv);
        $signature = $argv[0] ?? null;

        if (!$signature || !isset($this->commands[$signature])) {
            $keys = array_keys($this->commands);

            $matched = Argument::checkMatch($signature, $keys);

            if (empty($matched)) {
                Message::error("INVALID COMMAND!");
                echo "\n\tSUGGESTION: php laika help\n";
                return 1;
            }

            Message::error("INVALID COMMAND: <{$command}>");
            echo "\nPartial Matched Signatures Are:";
            echo "\n-----------------------------\n";
            foreach ($matched as $sig) {
                echo "-- {$sig}\n";
            }
            return 1;
        }

        $args = array_slice($argv, 1);

        try {
            return $this->commands[$signature]->handle($args, $this->basePath);
        } catch (\Throwable $e) {
            Message::error($e->getMessage());
            return 1;
        }
    }
}
