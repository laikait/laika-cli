<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Config;
use Laika\Cli\Stub;

class SecretGenerateCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'generate:secret';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        // Check Secret File Exist
        if (!Config::has('secret')) {
            Message::error("[secret] Config File Missing");
        }

        // Get Byte Number
        $byte = Argument::getValue('--byte', $args, 32);

        if (!is_numeric($byte)) {
            Message::error("Byte Should Be Numeric");
            return 1;
        }

        $byte = (int) $byte;
        if (($byte < 16) || ($byte > 64)) {
            Message::error("Byte Should Be Between 16 to 64");
            return 1;
        }

        try {
            Config::set('secret', 'key', bin2hex(random_bytes($byte)));
            Message::success("{$byte} Byte Secret Key Generated Successfully");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika generate:secret [--byte=32]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Generate new secret key',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['byte' => 'Number of bytes to generate. Default is 32. Range 16 to 64']
        ];
    }
}
