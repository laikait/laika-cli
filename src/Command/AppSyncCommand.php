<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Service\Infra;
use Laika\Service\File;
use Laika\Service\Directory;

class AppSyncCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'app:sync';
    }

    public function description(): string
    {
        return 'Sync app files';
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

        // Sync .HTACCESS
        $app_ht_file = "{$basePath}/.htaccess";
        if (!File::exists($app_ht_file)) {
            $app_ht_content = <<<HTCON
            ##################################################################
            ############## PLEASE DO NOT EDIT AFTER THIS LINE ################
            ##################################################################
            <IfModule mod_rewrite.c>
                RewriteEngine On
                
                # Handle Authorization Header
                RewriteCond %{HTTP:Authorization} .
                RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

                RewriteCond %{REQUEST_FILENAME} !-f
                RewriteRule ^ index.php [QSA,L]
            </IfModule>
            ##################################################################
            ################## YOU CAN ADD AFTER THIS LINE ###################
            ##################################################################
            HTCON;
        }
        try {
            File::write($app_ht_content, $app_ht_file);
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
        }

        Message::success("App Sync Successfull.");

        return 0;
    }

    public function command(): string
    {
        return "php laika app:sync";
    }

    public function help(): string
    {
        return <<<HELP
        APP SYNC COMMAND

            COMMAND     :   {$this->command()}

            INPUTS      :   No inputs available

            PARAMETERS  :   No parameters available

        HELP;
    }
}
