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
     * @param bool $default Default Value
     * @return bool
     */
    public static function getBool(string $key, array $args, bool $default = false): bool
    {
        if (!str_starts_with($key, '--')) return $default;

        foreach ($args as $arg) {
            if ($arg === $key) return true;
        }
        return $default;
    }

    /**
     * Match Command Key
     * @param ?string $key Command Key To Match
     * @return array{success:bool,message:?string}
     */
    public static function match(?string $key, array $list): array
    {
        // 1. Exact match
        if (in_array(strtolower((string) $key), $list)) {
            return [
                'success' => true,
                'message' => "Command [{$key}] found",
            ];
        }

        // 2. Find Closest Key
        $closestKey = null;
        $shortestDistance = PHP_INT_MAX;

        foreach ($list as $existingKey) {
            $distance = levenshtein($key, $existingKey);
            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $closestKey = $existingKey;
            }
        }

        // 3. Decide If Suggestion Is Good Enough
        // You Can Tune The Threshold (here <= 2)
        if ($shortestDistance <= 3) {
            return [
                'success' => false,
                'message' => "Laika suggested command:\n\n\t '{$closestKey}'\n\nfor help, run 'php laika help'",
            ];
        }

        return [
            'success' => false,
            'message' => "Invalid command \n\n\t'{$key}'\n\nfor help, run 'php laika help'",
        ];
    }

    /**
     * Readline Action Status
     * @param string $message Readline Message. Example: 'Confirm?'
     * @param array $accepted Accepted Input. Default is ['y','n']
     * @param array $accepted Accepted Input. Default is ['y','n']
     * @return bool
     */
    public static function readline(string $message, array $accepted = ['y','n'], string|array $positive = 'y'): bool
    {
        $message = trim($message);
        $accepted = array_map('strtolower', $accepted);
        $positive = array_map('strtolower', (array) $positive);
        $attampt = 0;
        $action = false;
        while (true) {
            if ($attampt === 10) {
                Message::error("Action failed. Tried maximum attampt {$attampt}");
                exit();
            }
            $status = readline($message . ' ' . implode('/', $accepted) . ': ');
            $status = strtolower($status);
            if (in_array($status, $accepted)) {
                if (in_array($status, $positive)) $action = true;
                break;
            } else {
                echo "Invalid input. Please enter " . implode('/', $accepted) . "\n";
            }
            $attampt++;
        }

        return $action;
    }
}
