<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class Argument
{
    /**
     * Get Argumment Value. Example: Get Value From --arg=value
     * @param string $key Key Name to Get Value
     * @param array $args Input Arguments
     * @param null|int|string $default Default Value
     * @return null|int|string
     */
    public static function getValue(string $key, array $args, null|int|string $default = null): null|int|string
    {
        if (!str_starts_with($key, '--')) return $default;

        foreach ($args as $arg) {
            if (str_starts_with($arg, "{$key}=")) return substr($arg, strlen("{$key}="));
        }
        return $default;
    }

    /**
     * Get Argumment Boolean Value. Example: Get Value From --enable
     * @param string $key Key Name to Get Value
     * @param array $args Input Arguments
     * @return bool
     */
    public static function getBool(string $key, array $args): bool
    {
        if (!str_starts_with($key, '--')) return false;

        foreach ($args as $arg) {
            if ($arg === $key) return true;
        }
        return false;
    }
}
