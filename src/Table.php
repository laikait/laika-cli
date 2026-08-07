<?php

declare(strict_types=1);

namespace Laika\Cli;

use Laika\Cli\Command\Message;

/**
 * Shared renderer for every `list:*` (and `queue:failed`) command's output
 * — a titled, colored, auto-width table instead of each command hand-rolling
 * its own str_repeat()/printf() dashes.
 *
 * Column widths are always measured off the plain cell text, then color is
 * applied to the already-padded string — never the other way around. Wrap a
 * cell in ANSI codes first and pad it after, and the invisible escape bytes
 * count toward the width, so the visible text pads short and the header row
 * silently drifts out of alignment with the data rows under it.
 */
class Table
{
    /**
     * @param string $title e.g. "CONTROLLER CLASSES"
     * @param string[] $headers column headers, e.g. ['# SL', '# CONTROLLER CLASS']
     * @param array<int,array<int,string|int>> $rows each row: a list of cells, same count as $headers
     */
    public static function render(string $title, array $headers, array $rows): void
    {
        $widths = array_map('strlen', $headers);
        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen((string) $cell));
            }
        }

        $headLine = self::line($headers, $widths);
        $bar = str_repeat('=', max(strlen($headLine), strlen($title) + 4));

        echo "\n{$bar}\n";
        echo '  ' . Message::txt_yellow($title) . "\n";
        echo "{$bar}\n";
        echo Message::txt_green($headLine) . "\n";
        echo str_repeat('-', strlen($headLine)) . "\n";

        foreach ($rows as $row) {
            echo self::line($row, $widths) . "\n";
        }

        echo str_repeat('-', strlen($headLine)) . "\n";
        echo '  Total: ' . count($rows) . " record(s)\n\n";
    }

    /** @param array<int,string|int> $cells @param int[] $widths */
    protected static function line(array $cells, array $widths): string
    {
        $parts = [];
        foreach (array_values($cells) as $i => $cell) {
            $parts[] = str_pad((string) $cell, $widths[$i] ?? strlen((string) $cell));
        }

        return '  ' . implode(' | ', $parts);
    }
}
