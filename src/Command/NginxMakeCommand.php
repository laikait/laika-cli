<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class NginxMakeCommand implements CommandInterface
{
    /** @var string Everything after this line belongs to the user, not the framework */
    public const USER_MARKER = 'YOU CAN EDIT AFTER THIS LINE';

    public function signature(): string
    {
        return 'nginx:make';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 2) {
            Message::suggestion($this->command());
            return 1;
        }

        $print = Argument::getBool('print', $args);
        $force = Argument::getBool('force', $args);

        $file = "{$basePath}/nginx.conf";
        $content = Stub::load('nginx');

        // Carry the user's own rules across, unless asked to reset them
        if (!$force && is_file($file)) {
            $custom = $this->customSection((string) file_get_contents($file));
            if ($custom !== '') {
                $content = rtrim($content) . "\n\n" . $custom . "\n";
            }
        }

        if ($print) {
            echo $content;
            return 0;
        }

        try {
            Stub::write($file, $content);
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        Message::success('nginx.conf written.');
        Message::info('Include it inside your server block, above any "location ~ \.php$" handler:');
        Message::info("    include {$basePath}/nginx.conf;");
        Message::info('Run `php laika nginx:server` for a complete server block, then `nginx -t` before reloading.');

        return 0;
    }

    ######################################################################################
    ## --------------------------------- INTERNAL API --------------------------------- ##
    ######################################################################################

    /**
     * Extract Whatever The User Added Below The Managed Region
     * @param string $existing Current file contents
     * @return string
     */
    private function customSection(string $existing): string
    {
        $position = strpos($existing, self::USER_MARKER);

        if ($position === false) {
            return '';
        }

        $lines = explode("\n", substr($existing, $position));

        // Drop the marker line itself, plus the "####" line that closes the banner
        array_shift($lines);
        if (isset($lines[0]) && str_starts_with(ltrim($lines[0]), '#')) {
            array_shift($lines);
        }

        return trim(implode("\n", $lines));
    }

    public function command(): string
    {
        return "php laika nginx:make [--print] [--force]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Write nginx.conf with the framework-managed location and deny rules',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [
                                    'print' =>  'Print the result instead of writing the file',
                                    'force' =>  'Also discard anything below the "YOU CAN EDIT" marker',
                                ]
        ];
    }
}
