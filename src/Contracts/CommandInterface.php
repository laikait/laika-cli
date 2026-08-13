<?php

declare(strict_types=1);

namespace Laika\Cli\Contracts;

interface CommandInterface
{
    public function signature(): string;

    public function handle(array $args, string $basePath): int;

    public function command(): string;

    public function help(): array;
}
