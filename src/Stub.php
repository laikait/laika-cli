<?php

declare(strict_types=1);

namespace Laika\Cli;

class Stub
{
    public static function load(string $name): string
    {
        $path = __DIR__ . '/../stubs/' . $name . '.stub';

        if (!is_file($path)) {
            throw new \RuntimeException("Stub not found: {$name}");
        }

        return file_get_contents($path);
    }

    public static function render(string $name, array $replacements): string
    {
        $content = static::load($name);

        foreach ($replacements as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    public static function write(string $path, string $content): void
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (is_file($path)) {
            throw new \RuntimeException("File already exists: {$path}");
        }

        file_put_contents($path, $content);
    }
}
