<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

class AppStartCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'app:start';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 2) {
            Message::suggestion($this->command());
            return 1;
        }
        $host = Argument::getValue('host', $args, '127.0.0.1');
        $port = Argument::getValue('port', $args, '8000');

        // Check Port is Numeric
        if (!preg_match('/^\d+$/', $port)) {
            Message::error("Port should be numeric!");
            return 1;
        }

        // Check Port is in Range
        if (!in_array((int) $port, range(1, 65535))) {
            Message::error("Invalid Port [{$port}]!");
            return 1;
        }

        // Get Next port if Not Available
        $port = $this->findAvailablePort((int) $port);

        echo "Laika development server started: http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop.\n\n";

        $command = sprintf('php -S %s:%s', escapeshellarg($host), escapeshellarg((string) $port));

        passthru($command, $exitCode);

        return $exitCode;
    }

    public function command(): string
    {
        return "php laika app:start";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Start the Laika development server',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }

    protected function findAvailablePort(int $port): int
    {
        while ($port <= 10000) {
            $output = (PHP_OS_FAMILY === 'Windows') ? shell_exec("netstat -ano | findstr :{$port} 2>NUL") : shell_exec("ss -tln 2>/dev/null | grep :{$port}");

            if (!$output) {
                break;
            }

            echo "Port [{$port}] is busy! Trying next port: [" . ($port + 1) . "]\n";
            $port++;
        }

        return $port;
    }
}
