<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Laika\Service\Cache;
use Throwable;

class CacheForgetCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'cache:forget';
    }

    public function handle(array $args, string $basePath): int
    {
        $key = Argument::getValue('key', $args);

        if ($key === null || trim((string) $key) === '') {
            Message::suggestion($this->command());
            return 1;
        }

        $key = (string) $key;

        try {
            $existed = Cache::has($key);

            if (!Cache::pop($key)) {
                Message::error("Unable to remove [{$key}].");
                return 1;
            }
        } catch (Throwable $e) {
            Message::error($e->getMessage());
            return 1;
        }

        $existed
            ? Message::success("Removed [{$key}] from the cache.")
            : Message::info("[{$key}] was not cached.");

        return 0;
    }

    public function command(): string
    {
        return "php laika cache:forget --key=name";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Remove one key from the data cache',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  ['key' => 'Cache key to remove']
        ];
    }
}
