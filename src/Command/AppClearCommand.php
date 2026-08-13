<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Service\Resource;

class AppClearCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'app:clear';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        $file = Resource::manifestPath();

        if (!is_file($file)) {
            Message::info('No resource manifest to clear.');
            return 0;
        }

        if (!unlink($file)) {
            Message::error("Unable to remove [{$file}].");
            return 1;
        }

        Message::success('Resource manifest cleared. Resources are discovered from disk again.');
        return 0;
    }

    public function command(): string
    {
        return "php laika app:clear";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Remove the compiled resource manifest',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
