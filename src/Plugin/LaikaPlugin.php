<?php

declare(strict_types=1);

namespace Laika\Cli\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Plugin\PluginInterface;
use Laika\Cli\Stub;
use Composer\EventDispatcher\EventSubscriberInterface;

class LaikaPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        // Nothing to bootstrap eagerly — all the work happens on the
        // subscribed events below.
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'onPostAutoloadDump',
        ];
    }

    /**
     * Generate the `laika` executable in the project root right after
     * Composer (re)builds the autoloader.
     *
     * This only fires for an actual Laika Framework project (one that
     * ships lf-boot/app.php). It is a safe no-op for a plain/global
     * install of this package, or for any project that isn't Laika.
     */
    public static function onPostAutoloadDump(Event $event): void
    {
        $io = $event->getIO();
        $vendorDir = rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/\\');
        $projectRoot = dirname($vendorDir);

        if (!is_file($projectRoot . '/lf-boot/app.php')) {
            return; // Not a Laika Framework project root, nothing to do.
        }

        try {
            $content = Stub::load('entry');
        } catch (\Throwable $e) {
            $io->writeError("<warning>Laika CLI: unable to load entry stub — {$e->getMessage()}</warning>");
            return;
        }

        $target = $projectRoot . '/laika';

        if (is_file($target) && file_get_contents($target) === $content) {
            return; // Already up to date.
        }

        file_put_contents($target, $content);
        @chmod($target, 0755);

        $io->write('<info>Laika CLI:</info> generated executable "laika" in project root.');
    }
}
