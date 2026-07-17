<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

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

        // Create .key File If Doesn't Exists
        $file = "{$basePath}/lf-storage/.key";
        if (!is_file($file)) touch($file);

        // Get Byte Number
        $byte = Argument::getValue('byte', $args, '32');

        // Validate Byte is Numeric & In Range
        if (!preg_match('/^\d+$/', $byte)) {
            Message::error("Byte should be numeric!");
            return 1;
        }

        if (!in_array($byte, range(16,64))) {
            Message::error("Byte should be between 16 to 64");
            return 1;
        }

        // Generate Key
        $key = base64_encode(bin2hex(random_bytes(16)) . '-' . bin2hex(random_bytes((int) $byte)));
        try {
            file_put_contents($file, $key);
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        try {
            chmod($file, 0600);
        } catch (\Throwable $th) {}
        Message::success("{$byte} Byte Secret Key Generated Successfully");

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
