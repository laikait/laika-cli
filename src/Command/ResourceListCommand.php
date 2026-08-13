<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
use Throwable;
use Laika\Cli\Table;
use Laika\Service\Resource;

class ResourceListCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'resource:list';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) > 1) {
            Message::suggestion($this->command());
            return 1;
        }

        $filter = Argument::getValue('name', $args);
        $definitions = Resource::definitions();

        if ($filter !== null) {
            $filter = strtolower((string) $filter);
            $definitions = array_values(array_filter(
                $definitions,
                static fn ($d): bool => $d->name === $filter
            ));

            if (empty($definitions)) {
                Message::error("No resource registered under [{$filter}].");
                Message::info('Registered: ' . implode(', ', Resource::names()));
                return 1;
            }
        }

        if (empty($definitions)) {
            Message::info('No resources registered!');
            return 0;
        }

        $missing = 0;
        $names = [];
        $rows = [];

        foreach ($definitions as $definition) {
            $names[$definition->name] = true;
            $exists = $definition->exists();

            if (!$exists) {
                $missing++;
            }

            $rows[] = [
                $definition->name,
                $definition->source,
                $definition->namespace ?? '(files)',
                $this->relative($definition->path, $basePath),
                // What this location contributes, not the total for the name
                $exists ? (string) count(Resource::entries($definition)) : 'MISSING'
            ];
        }

        Table::render(
            'REGISTERED RESOURCES',
            ['# NAME', '# SOURCE', '# NAMESPACE', '# PATH', '# ENTRIES'],
            $rows
        );

        if ($missing) {
            Message::warning("{$missing} path(s) marked MISSING do not exist yet and resolve to nothing.");
        }

        // Surface class names that don't load or don't satisfy their contract.
        // File resources have nothing to validate.
        $broken = 0;
        foreach (array_keys($names) as $name) {
            if (!Resource::isClassMap($name)) {
                continue;
            }

            try {
                Resource::getClasses($name);
            } catch (Throwable $e) {
                Message::error("[{$name}] " . $e->getMessage());
                $broken++;
            }
        }

        return $broken ? 1 : 0;
    }

    /**
     * Shorten a Path For Display
     * @param string $path
     * @param string $basePath
     * @return string
     */
    private function relative(string $path, string $basePath): string
    {
        $base = realpath($basePath) ?: $basePath;
        return str_starts_with($path, $base) ? trim(substr($path, strlen($base)), '/\\') : $path;
    }

    public function command(): string
    {
        return "php laika resource:list";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'List every registered resource, where it was declared and how many entries it resolves to',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [
                '--name'    =>  'Show only the given resource. Example: --name=models'
            ]
        ];
    }
}
