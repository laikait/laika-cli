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

    /**
     * Render Stub
     * @param string $name Stub Name
     * @param array<string,string> $replacements
     */
    public static function render(string $name, array $replacements): string
    {
        $content = static::load($name);
        return str_replace(
            array_map( fn ($k) => "{{{$k}}}", array_keys($replacements)),
            array_values($replacements),
            $content
        );
    }

    public static function write(string $path, string $content): void
    {
        $dir = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($path, $content);
    }
}
