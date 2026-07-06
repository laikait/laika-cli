<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

interface CommandInterface
{
    public function signature(): string;

    public function description(): string;

    public function handle(array $args, string $basePath): int;
}
