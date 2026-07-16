<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Config;
use Laika\Cli\Stub;

class SecretFixCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'fix:secret';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
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

        // Ceate if Secret File Doesn't Exist
        if (!Config::has('secret')) {
            Config::create('secret', ['key' => bin2hex(random_bytes($byte))]);
            Message::success("{$byte} Byte Secret Key Generated.");
            return 0;
        }

        // Create If Not Valid
        $key = trim((string) Config::get('secret', 'key'));
        if (!$key || (strlen($key) != $byte * 2)) {
            Config::set('secret', 'key', bin2hex(random_bytes($byte)));
            Message::success("{$byte} Byte Secret Key Regenerated Successfully");
            return 0;
        }

        Message::info("Secret key remains unchanged");

        return 0;
    }

    public function command(): string
    {
        return "php laika fix:secret [--byte=number]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Fix secret key',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['byte' => 'Number of bytes to generate. Default is 32. Range 16 to 64']
        ];
    }
}
