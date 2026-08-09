<?php

declare(strict_types=1);

namespace Laika\Cli;

use Composer\Script\Event;

class ScriptHandler
{
    /**
     * Generate the `laika` executable — and, on Windows, its `laika.bat`
     * shim — in the project root.
     *
     * Wired from the root project's composer.json "scripts" section, since
     * Composer never runs a dependency's own scripts:
     *
     *   "post-autoload-dump": [
     *       "Laika\\Cli\\ScriptHandler::generate",
     *       ...
     *   ]
     *
     * Safe no-op for anything that isn't a Laika Framework project root
     * (a global install, or CI for the package itself).
     *
     * Stubs are read straight off disk relative to this file rather than
     * through Stub::load(). A global `composer global require` of this
     * package registers its own autoloader first, so `Laika\Cli\Stub` may
     * resolve to a different installation than this one — and would then
     * read that installation's stubs/ directory.
     */
    public static function generate(Event $event): void
    {
        $io          = $event->getIO();
        $vendorDir   = rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/\\');
        $projectRoot = dirname($vendorDir);

        if (!is_file($projectRoot . '/lf-boot/app.php')) {
            return; // Not a Laika Framework project root, nothing to do.
        }

        $written = [];

        foreach (static::targets($projectRoot) as $target => $stub) {
            $source = __DIR__ . '/../stubs/' . $stub . '.stub';

            if (!is_file($source)) {
                $io->writeError("<warning>Laika CLI: stub not found — {$source}</warning>");
                continue;
            }

            $content = file_get_contents($source);

            if (is_file($target) && file_get_contents($target) === $content) {
                continue; // Already up to date.
            }

            file_put_contents($target, $content);
            @chmod($target, 0755);

            $written[] = basename($target);
        }

        if ($written) {
            $io->write('<info>Laika CLI:</info> generated ' . implode(', ', $written) . ' in project root.');
        }
    }

    /**
     * Target files to generate, keyed by absolute path.
     * @return array<string,string> path => stub name
     */
    protected static function targets(string $root): array
    {
        $targets = [$root . '/laika' => 'entry'];

        if (PHP_OS_FAMILY === 'Windows') {
            $targets[$root . '/laika.bat'] = 'entry-bat';
        }

        return $targets;
    }
}
