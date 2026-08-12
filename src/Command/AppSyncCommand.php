<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;
use Laika\Service\File;
use Laika\Service\Infra;
use Laika\Service\AppKey;
use Laika\Service\Directory;
use Laika\Service\Resource;

class AppSyncCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'app:sync';
    }

    public function handle(array $args, string $basePath): int
    {
        if (count($args) != 0) {
            Message::suggestion($this->command());
            return 1;
        }

        foreach (Directory::scan("{$basePath}/lf-cache", true, 'php') as $f) {
            try {
                if (File::exists($f)) {
                    File::pop($f);
                } elseif (Directory::exists($f)) {
                    Directory::pop($f);
                }
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
                return 1;
            }
        }

        // Make Uploads Directory
        Directory::make(APP_PATH . DS . 'uploads');

        // Sync .HTACCESS
        $app_ht_file = $basePath . DS . '.htaccess';
        if (!File::exists($app_ht_file)) {
            
            try {
                $content = Stub::load('htaccess');
                Stub::write($app_ht_file, $content);
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
                return 1;
            }
        }

        // Sync Deny Directories HTASCCESS
        $dirs = ['lf-app', 'lf-boot', 'lf-cache', 'lf-config', 'lf-hooks', 'lf-inc', 'lf-lang', 'lf-logs', 'lf-routes', 'lf-storage'];
        $hc = Stub::load('htaccess-deny');
        foreach ($dirs as $d) {
            $dir = $basePath . DS . $d;
            $ht_file = $dir . DS . '.htaccess';
            // Make Directory If Doesn't Exists
            Directory::make($dir);
            if (File::exists($ht_file)) continue;
            try {
                Stub::write($ht_file, $hc);
                setPermission($ht_file, 0640);
            } catch (\Throwable $e) {
                Message::error($e->getMessage());
                return 1;
            }
        }

        // Fix App Key
        try {
            AppKey::fix();
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        // Refresh The Resource Manifest If One Is In Use. The cache wipe above no
        // longer touches it, but dependencies may have changed since it was built.
        $cache_dir = $basePath . DS . 'lf-storage' . DS . 'cache';
        Directory::make($cache_dir);
        // Create Manifest Path if Doesn't Exists
        if (!File::exists(Resource::manifestPath())) File::touch(Resource::manifestPath());
        try {
            Resource::cache();
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        Message::success("App Sync Successfull.");

        return 0;
    }

    public function command(): string
    {
        return "php laika app:sync";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Sync app files and other settings',
            'command'       =>  $this->command(),
            'inputs'        =>  [],
            'params'        =>  []
        ];
    }
}
