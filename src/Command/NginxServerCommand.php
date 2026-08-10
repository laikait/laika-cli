<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;

class NginxServerCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'nginx:server';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 7) {
            Message::suggestion($this->command());
            return 1;
        }

        $domain = (string) Argument::getValue('domain', $args, 'localhost');
        if (!preg_match('/^[a-z0-9_.\-* ]+$/i', $domain)) {
            Message::error("Invalid domain [{$domain}].");
            return 1;
        }

        $port = (string) Argument::getValue('port', $args, '80');
        if (!preg_match('/^\d{1,5}$/', $port) || (int) $port < 1 || (int) $port > 65535) {
            Message::error("Invalid port [{$port}].");
            return 1;
        }

        // Either an explicit upstream, or one derived from a PHP version
        $fastcgi = (string) Argument::getValue('fastcgi', $args, '');
        if ($fastcgi === '') {
            $php = (string) Argument::getValue('php', $args, implode('.', array_slice(explode('.', PHP_VERSION), 0, 2)));
            if (!preg_match('/^\d+\.\d+$/', $php)) {
                Message::error("Invalid PHP version [{$php}]. Example: --php=8.3");
                return 1;
            }
            $fastcgi = "unix:/var/run/php/php{$php}-fpm.sock";
        }

        // Defaults to this machine's project path, which is wrong when the
        // config is generated on one host and deployed to another.
        $root = (string) Argument::getValue('root', $args, $basePath);

        $content = Stub::render('nginx-server', [
            'domain'    =>  $domain,
            'port'      =>  $port,
            // nginx wants forward slashes even when the CLI runs on Windows
            'root'      =>  str_replace('\\', '/', rtrim($root, '/\\')),
            'fastcgi'   =>  $fastcgi,
            'upload'    =>  (string) Argument::getValue('upload', $args, '20M'),
        ]);

        $output = (string) Argument::getValue('output', $args, '');

        if ($output === '') {
            echo $content;
            Message::info('Pass --output=<file> to write this instead of printing it.');
            return 0;
        }

        try {
            Stub::write($output, $content);
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        Message::success("Server block written to {$output}.");
        Message::info('Check it before reloading: nginx -t && systemctl reload nginx');

        return 0;
    }

    public function command(): string
    {
        return "php laika nginx:server [--domain=example.com] [--port=80] [--php=8.3] [--fastcgi=...] [--root=/var/www/app] [--upload=20M] [--output=file]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Generate a complete nginx server block for this project',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [
                                    'domain'    =>  'server_name value (default: localhost)',
                                    'port'      =>  'Listen port (default: 80)',
                                    'php'       =>  'PHP-FPM version used to build the socket path (default: this PHP)',
                                    'fastcgi'   =>  'Explicit upstream, overrides --php. Example: 127.0.0.1:9000',
                                    'root'      =>  'Project path on the target server (default: this machine\'s path)',
                                    'upload'    =>  'client_max_body_size (default: 20M)',
                                    'output'    =>  'Write to this file instead of printing',
                                ]
        ];
    }
}
