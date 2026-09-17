<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use FilesystemIterator;
use Laika\Cli\Contracts\CommandInterface;
use Laika\Service\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class CacheClearCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'cache:clear';
    }

    public function handle(array $args, string $basePath): int
    {
        $data = !Argument::getBool('templates', $args);
        $templates = !Argument::getBool('data', $args);

        if (!$data && !$templates) {
            Message::error('--data and --templates are mutually exclusive.');
            return 1;
        }

        if ($data) {
            try {
                $driver = strtolower((string) (Cache::config()['driver'] ?? 'file'));

                if (!Cache::flush()) {
                    Message::error("Unable to flush the [{$driver}] cache.");
                    return 1;
                }
            } catch (Throwable $e) {
                Message::error($e->getMessage());
                return 1;
            }

            Message::success("Data cache flushed ({$driver} driver).");

            // Memcached has no prefix scan, so its flush is server-wide
            if ($driver === 'memcached') {
                Message::info('Memcached cannot flush by prefix: every key on that server was cleared.');
            }
        }

        if ($templates) {
            // Nothing else clears compiled Twig: app:clear only removes the
            // resource manifest, and with DEBUG off Twig never recompiles on
            // its own, so an edited template keeps serving the old build.
            $removed = $this->emptyDirectory(TEMPLATE_CACHE_PATH);

            if ($removed === false) {
                Message::error('Unable to clear [' . TEMPLATE_CACHE_PATH . '].');
                return 1;
            }

            Message::success("Compiled templates cleared ({$removed} file(s)).");
        }

        return 0;
    }

    public function command(): string
    {
        return "php laika cache:clear [--data|--templates]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Flush the data cache and the compiled Twig templates',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  [
                                    'data'      =>  'Flush only the data cache',
                                    'templates' =>  'Clear only lf-storage/cache/template',
                                ]
        ];
    }

    /**
     * Remove Everything Under a Directory, Keeping The Directory
     *
     * Recursive: Template::view() compiles each sub-directory's views into its
     * own folder under the cache root.
     *
     * @param string $path
     * @return int|false Files removed, false when something could not be removed
     */
    protected function emptyDirectory(string $path): int|false
    {
        if (!is_dir($path)) {
            return 0;
        }

        $removed = 0;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            // Never follow a link out of the cache directory
            if ($item->isLink() || $item->isFile()) {
                // Read before unlinking: afterwards there is nothing to stat
                $isFile = !$item->isLink() && $item->isFile();

                if (!@unlink($item->getPathname())) {
                    return false;
                }
                $removed += (int) $isFile;
            } elseif ($item->isDir() && !@rmdir($item->getPathname())) {
                return false;
            }
        }

        return $removed;
    }
}
