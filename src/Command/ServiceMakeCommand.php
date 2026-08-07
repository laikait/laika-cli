<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;
use Laika\Service\Infra;

class ServiceMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'service:make';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 2) {
            Message::suggestion($this->command());
            return 1;
        }

        $service = ucfirst(trim(Argument::getValue('name', $args, ''), '\\'));
        $relay = trim(Argument::getValue('class', $args, ''), '\\');
        
        // Validate Inputs
        if (empty($service)) {
            Message::error("Input [--name] should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_]+$/i', $service)) {
            Message::error("Invalid service name [{$service}].");
            return 1;
        }
        if (empty($relay)) {
            Message::error("Input [--class] should not be empty.");
            return 1;
        }
        if (!preg_match('/^[a-z_\\\\]+$/i', $relay)) {
            Message::error("Invalid relay class [{$relay}].");
            return 1;
        }

        $service_path = $basePath . "/lf-app/Service/{$service}.php";
        $relay_path = $basePath . "/lf-app/Relay/{$service}.php";

        // Check Service Class Doesn't Exists
        if (is_file($service_path)) {
            Message::error("App service [{$service}] already exists.");
            return 1;
        }

        // Check App Relay Class Doesn't Exists
        if (is_file($relay_path)) {
            Message::error("App relay [{$service}] already exists.");
            return 1;
        }

        // Check User Input Relay Class Exists
        if (!class_exists($relay)) {
            Message::error("Input relay class [{$relay}] doesn't exists.");
            return 1;
        }

        // Check Accessor Not Used Yet.
        $accessor = strtolower("{$service}.accessor");
        if (array_key_exists($accessor, Infra::getRelayClasses())) {
            Message::error("Accessor {$accessor} ALready Exists.");
            return 1;
        }
        
        // Make App Service & Relay Replace Array
        $service_array = [
            'class'     =>  $service,
            'accessor'  =>  $accessor
        ];

        $parts = explode('\\', $relay);
        $relay_array = [
            'relay_class'   =>  $relay,
            'class'         =>  $service,
            'accessor'      =>  strtolower("{$service}.accessor"),
            'accessor_class'=>  end($parts)
        ];

        try {
            // Get Stub Contents
            $service_content = Stub::render('service', $service_array);
            $relay_content = Stub::render('relay', $relay_array);
            // Write Contents
            Stub::write($service_path, $service_content);
            Stub::write($relay_path, $relay_content);

            Message::success("Service [{$service}] created successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika service:make <--name=ServiceClass> <--class=RelayClass>";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new service class',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [
                                    'name'  =>  'Relay provider class name. Example: Template',
                                    'class' =>  'Service provider class name. Example: Laika\Core\App\Template'
                                ]
        ];
    }
}
