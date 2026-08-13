<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Throwable;
use Laika\Service\Resource;

class AppCacheCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'app:cache';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        try {
            $file = Resource::cache();
        } catch (Throwable $e) {
            Message::error($e->getMessage());
            return 1;
        }

        $resources = Resource::getResources();
        $total = array_sum(array_map('count', $resources));

        Message::success(
            sprintf(
                'Cached %d resource(s) totalling %d entries to %s',
                count($resources),
                $total,
                trim(str_replace($basePath, '', $file), '/\\')
            )
        );
        Message::info('The manifest is used when DEBUG is false. Re-run this after adding or moving components.');

        return 0;
    }

    public function command(): string
    {
        return "php laika app:cache";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Compile every registered resource into lf-storage/cache/resources.php',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
