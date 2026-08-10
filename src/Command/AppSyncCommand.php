<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;
use Laika\Service\File;
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

        // Clear Base Path Junk Files
        $files = [
            "{$basePath}/jp.php",
            "{$basePath}/jp.php.bat"
        ];

        $files = array_merge($files, Directory::scan("{$basePath}/lf-cache", true, 'php'));

        foreach ($files as $f) {
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
        Directory::make(APP_PATH . '/uploads');

        // Sync .HTACCESS
        $app_ht_file = "{$basePath}/.htaccess";
        if (!File::exists($app_ht_file)) {
            $app_ht_content = <<<HTCONTENT
            ##################################################################
            ############## PLEASE DO NOT EDIT AFTER THIS LINE ################
            ##################################################################
            <IfModule mod_rewrite.c>
                RewriteEngine On
                
                # Handle Authorization Header
                RewriteCond %{HTTP:Authorization} .
                RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

                RewriteRule ^ index.php [QSA,L]
            </IfModule>
            ##################################################################
            ################## YOU CAN ADD AFTER THIS LINE ###################
            ##################################################################
            HTCONTENT;

            try {
                File::write($app_ht_content, $app_ht_file);
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
            }
        }

        // Deny Web Access To Private Directories. These are gitignored, so the
        // guard cannot be shipped in the repository — it has to be created here.
        // Note this only covers Apache; nginx needs an equivalent location block.
        $private_ht_content = <<<HTCONTENT
        <IfModule mod_authz_core.c>
            Require all denied
        </IfModule>
        <IfModule !mod_authz_core.c>
            Order allow,deny
            Deny from all
        </IfModule>
        HTCONTENT;

        foreach (['lf-storage', 'lf-cache'] as $private) {
            $dir = "{$basePath}/{$private}";
            Directory::make($dir);

            $ht_file = "{$dir}/.htaccess";
            if (File::exists($ht_file)) {
                continue;
            }

            try {
                File::write($private_ht_content, $ht_file);
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
            }
        }

        // Create app.key File If Doesn't Exists
        $dir = "{$basePath}/lf-storage/keys";
        if (!is_dir($dir)) {
            mkdir($dir, recursive: true);
            setPermission($dir, 0775);
        }
        $file = "{$dir}/app.key";
        if (!is_file($file)) touch($file);

        $keyContent = file_get_contents($file);
        if (empty($keyContent)) {
            // Generate Key
            $key = base64_encode(bin2hex(random_bytes(16)) . '-' . bin2hex(random_bytes(32)));
            try {
                setPermission($file, 0640);
                file_put_contents($file, $key);
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
                return 1;
            }

            setPermission($file, 0600);
        }

        // Refresh The Resource Manifest If One Is In Use. The cache wipe above no
        // longer touches it, but dependencies may have changed since it was built.
        if (File::exists(Resource::manifestPath())) {
            try {
                Resource::cache();
            } catch (\Throwable $th) {
                Message::error($th->getMessage());
                return 1;
            }
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
