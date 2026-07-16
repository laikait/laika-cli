<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Cli\Command\Message;
use Laika\Cli\Command\Argument;
use Laika\Cli\Command\HelpCommand;
use Laika\Cli\Command\CommandInterface;
use Laika\Cli\Command\FilterListCommand;
use Laika\Cli\Command\AppSyncCommand;
use Laika\Cli\Command\RouteListCommand;
use Laika\Cli\Command\PipelineMakeCommand;
use Laika\Cli\Command\PipelineRenameCommand;
use Laika\Cli\Command\FilterMakeCommand;
use Laika\Cli\Command\FilterRemoveCommand;
use Laika\Cli\Command\FilterRenameCommand;
use Laika\Cli\Command\ModelMakeCommand;
use Laika\Cli\Command\TemplateMakeCommand;
use Laika\Cli\Command\ServiceMakeCommand;
use Laika\Cli\Command\ControllerMakeCommand;
use Laika\Cli\Command\ControllerListCommand;
use Laika\Cli\Command\ControllerRemoveCommand;
use Laika\Cli\Command\ControllerRenameCommand;
use Laika\Cli\Command\PipelineListCommand;
use Laika\Cli\Command\ModelListCommand;
use Laika\Cli\Command\SchemaListCommand;
use Laika\Cli\Command\RelayListCommand;
use Laika\Cli\Command\TemplateListCommand;
use Laika\Cli\Command\ModelRemoveCommand;
use Laika\Cli\Command\ModelRenameCommand;
use Laika\Cli\Command\PipelineRemoveCommand;
use Laika\Cli\Command\SecretFixCommand;
use Laika\Cli\Command\SecretGenerateCommand;
use Laika\Cli\Command\ServiceRemoveCommand;

class Application
{
    /** @var CommandInterface[] */
    protected array $commands = [];

    public function __construct(protected string $basePath)
    {
        // Help
        $this->register(new HelpCommand()); // Done
        // Service
        $this->register(new RelayListCommand); // Done
        $this->register(new ServiceMakeCommand()); // Done
        $this->register(new ServiceRemoveCommand()); // Done

        // Model
        $this->register(new ModelListCommand()); // Done
        $this->register(new ModelMakeCommand()); // Done
        $this->register(new ModelRemoveCommand()); // Done
        $this->register(new ModelRenameCommand()); // Done
        $this->register(new SchemaListCommand); // Done

        // Pipeline
        $this->register(new PipelineListCommand); // Done
        $this->register(new PipelineMakeCommand()); // Done
        $this->register(new PipelineRenameCommand()); // Done
        $this->register(new PipelineRemoveCommand()); // Done

        // Filter
        $this->register(new FilterListCommand); // Done
        $this->register(new FilterMakeCommand()); // Done
        $this->register(new FilterRenameCommand()); // Done
        $this->register(new FilterRemoveCommand()); // Done

        // Controller
        $this->register(new ControllerListCommand); // Done
        $this->register(new ControllerMakeCommand()); // Done
        $this->register(new ControllerRenameCommand()); // Done
        $this->register(new ControllerRemoveCommand()); // Done

        // Template
        $this->register(new TemplateListCommand);
        $this->register(new TemplateMakeCommand());

        // Route
        $this->register(new RouteListCommand()); // Done

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
