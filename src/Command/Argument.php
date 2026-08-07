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
        $key = '--' . ltrim(strtolower($key), '--');
        foreach ($args as $arg) {
            if (str_starts_with(strtolower($arg), "{$key}=")) return substr($arg, strlen("{$key}="));
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
        $key = '--' . ltrim(strtolower($key), '--');
        foreach ($args as $arg) {
            if (strtolower($arg) === $key) return true;
        }
        return $default;
    }

    /**
     * Check Matched Signatures
     * @param ?string $key Command Key To Match
     * @return array{success:bool,message:?string}
     */
    public static function checkMatch(?string $key, array $list): array
    {
        if ($key === null) {
            return [];
        }

        $partialMatches = array_values(array_filter(
            $list,
            fn ($existingKey) => str_starts_with($existingKey, $key) || str_contains($existingKey, $key)
        ));

        if ($partialMatches) {
            return $partialMatches;
        }

        $matchedKeys = [];
        $shortestDistance = PHP_INT_MAX;

        foreach ($list as $existingKey) {
            $distance = levenshtein($key, $existingKey);

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $matchedKeys = [$existingKey];
            } elseif ($distance === $shortestDistance) {
                $matchedKeys[] = $existingKey;
            }
        }

        if ($shortestDistance > 3) {
            return [];
        }

        return $matchedKeys;
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
            // readline() returns false (not a string) on EOF/no TTY — e.g.
            // this command run from a script or CI without input piped in.
            // Casting keeps that a normal failed-attempt loop instead of a
            // strtolower(): bool given TypeError.
            $status = strtolower((string) readline($message . ' ' . implode('/', $accepted) . ': '));
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

    /**
     * Validate Input
     * @param string[] $args
     * @param string[] $accepted
     * @return bool
     */
    public static function validateInputs(array $args, array $accepted): bool
    {
        $total = count($args);

        foreach ($accepted as $input) {
            foreach ($args as $arg) {
                if (!preg_match()) {
                    return false;
                }
            }
        }
    }
}
