<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

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

        // Create If Not Valid
        $key = base64_decode((string) file_get_contents($file));
        $newkey = base64_encode(bin2hex(random_bytes(16)) . '-' . bin2hex(random_bytes((int) $byte)));

        // Generate Key if Existing Key Not Found
        if (!$key) {
            file_put_contents($file, $newkey);
            Message::success("{$byte} byte secret key generated successfully");
            return 0;
        }

        // Generate Key if Key is Invalid
        $parts = explode('-', $key);
        if ((count($parts) != 2)) {
            file_put_contents($file, $newkey);
            Message::success("{$byte} byte secret key regenerated successfully");
            return 0;
        }
        if (strlen($parts[1]) != $byte * 2) {
            file_put_contents($file, $newkey);
            Message::success("{$byte} byte secret key regenerated successfully");
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
