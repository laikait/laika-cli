<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Directory;

class HelpCommand implements CommandInterface
{
    /**
     * Signature "verbs" — whichever side of a "a:b" signature matches one
     * of these is the action, and the other side is the resource it groups
     * under. Every command is <resource>:<action> today (job:list,
     * queue:work), but this checks both sides so a signature that isn't
     * still groups correctly. Add to this list if a new command introduces
     * a verb that isn't here yet.
     */
    protected const VERBS = [
        'list', 'make', 'remove', 'rename', 'migrate', 'start', 'sync',
        'generate', 'fix', 'work', 'failed', 'retry', 'flush',
    ];

    public function signature(): string
    {
        return 'help';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $list = $this->discover();

        return $args ? $this->renderDetail($list, $args[0]) : $this->renderIndex($list);
    }

    public function command(): string
    {
        return "php laika help [command]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Laika command help',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [],
        ];
    }

    /**
     * @return array<int,array{signature:string,description:string,command:string,inputs:array,params:array}>
     */
    protected function discover(): array
    {
        $files = Directory::files(__DIR__);
        $classes = array_map(fn ($f) => "\\Laika\\Cli\\Command\\" . pathinfo($f, PATHINFO_FILENAME), $files);

        $list = [];
        foreach ($classes as $c) {
            if ($c === '\Laika\Cli\Command\CommandInterface' || !method_exists($c, 'help')) {
                continue;
            }
            $list[] = (new $c())->help();
        }

        usort($list, fn ($a, $b) => $a['signature'] <=> $b['signature']);
        return $list;
    }

    /** Compact, grouped, colored index — the default `php laika help` view. */
    protected function renderIndex(array $list): int
    {
        $groups = [];
        foreach ($list as $item) {
            $groups[$this->groupOf($item['signature'])][] = $item;
        }

        // "GENERAL" (bare commands like `help`, no resource to group under)
        // reads better last, not wherever it happens to sort alphabetically.
        $general = $groups['GENERAL'] ?? null;
        unset($groups['GENERAL']);
        ksort($groups);
        if ($general) {
            $groups['GENERAL'] = $general;
        }

        $width = max(array_map(fn ($i) => strlen($i['signature']), $list)) + 2;
        $bar = str_repeat('=', 70);

        echo "\n{$bar}\n";
        echo '  ' . Message::txt_yellow('LAIKA CLI') . " :: COMMAND REFERENCE\n";
        echo "{$bar}\n";

        foreach ($groups as $group => $items) {
            echo "\n" . Message::txt_green("[{$group}]") . "\n";
            foreach ($items as $item) {
                $sig = str_pad($item['signature'], $width);
                echo '  ' . Message::txt_cyan($sig) . $item['description'] . "\n";
            }
        }

        echo "\n{$bar}\n";
        echo '  Total: ' . count($list) . " command(s)\n";
        echo '  Run ' . Message::txt_cyan('php laika help <command>') . " for full usage of one command.\n\n";

        return 0;
    }

    /** Full single-command breakdown — `php laika help <command>`. */
    protected function renderDetail(array $list, string $name): int
    {
        $bySignature = [];
        foreach ($list as $item) {
            $bySignature[$item['signature']] = $item;
        }

        if (!isset($bySignature[$name])) {
            $matched = Argument::checkMatch($name, array_keys($bySignature));

            if (empty($matched)) {
                Message::error("No help found for [{$name}]!");
                return 1;
            }

            if (count($matched) > 1) {
                Message::error("Ambiguous command [{$name}]. Did you mean:");
                foreach ($matched as $m) {
                    echo "  -- {$m}\n";
                }
                return 1;
            }

            $name = $matched[0];
        }

        $arr = $bySignature[$name];

        // Pad width has to fit the longest key actually printed below (raw
        // input names, "--"-prefixed param names) — a fixed guess is what
        // broke this for `--connection` before; derive it instead.
        $keys = [
            ...array_keys($arr['inputs']),
            ...array_map(fn ($k) => "--{$k}", array_keys($arr['params'])),
        ];
        $len = $keys ? max(array_map('strlen', $keys)) : 0;

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "SIGNATURE\t:\t" . Message::txt_yellow($arr['signature']) . "\n";
        echo "COMMAND\t\t:\t" . Message::txt_cyan($arr['command']) . "\n";
        echo "DESCRIPTION\t:\t{$arr['description']}\n";

        if (!empty($arr['inputs'])) {
            echo "INPUTS:\n";
            foreach ($arr['inputs'] as $k => $v) {
                $s = str_repeat(' ', max(0, $len - strlen($k)));
                echo "\t{$k}{$s}  :\t{$v}\n";
            }
        }

        if (!empty($arr['params'])) {
            echo "PARAMS:\n";
            foreach ($arr['params'] as $k => $v) {
                $s = str_repeat(' ', max(0, $len - strlen("--{$k}")));
                echo "\t--{$k}{$s}  :\t{$v}\n";
            }
        }

        echo str_repeat('=', 70) . "\n\n";

        return 0;
    }

    /** Derive a display group ("JOB", "QUEUE", "APP", ...) from a signature like "job:make" or "queue:work". */
    protected function groupOf(string $signature): string
    {
        if (!str_contains($signature, ':')) {
            return 'GENERAL';
        }

        [$a, $b] = explode(':', $signature, 2);
        $resource = in_array($a, self::VERBS, true) ? $b : (in_array($b, self::VERBS, true) ? $a : $b);

        return strtoupper($resource);
    }
}
