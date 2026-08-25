<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Contracts\CommandInterface;
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

        // Storage Link
        $storage_path = $basePath . DS . 'lf-storage';

        if (!is_file($storage_path . DS . '.htaccess')) {
            File::touch($storage_path . DS . '.htaccess');
            File::write('Deny from all', $storage_path . DS . '.htaccess');
            @chmod($storage_path . DS . '.htaccess', 644);
        }

        // Cache Dir
        $cache_dir = $storage_path . DS . 'cache';
        Directory::make($cache_dir);
        foreach (Directory::scan($cache_dir, true, 'php') as $f) {
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

        // Fix App Key
        try {
            AppKey::fix();
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }

        // Refresh The Resource Manifest If One Is In Use. The cache wipe above no
        // longer touches it, but dependencies may have changed since it was built.
        
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
