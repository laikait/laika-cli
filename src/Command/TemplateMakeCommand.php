<?php

declare(strict_types=1);

namespace Laika\Cli\Command;

use Laika\Cli\Stub;
use Laika\Service\Directory;

class TemplateMakeCommand implements CommandInterface
{
    public function signature(): string
    {
        return 'make:template';
    }

    public function handle(array $args, string $basePath): int
    {
        if (!in_array(count($args), range(1,3))) {
            Message::suggestion($this->command());
            return 0;
        }

        // Get Template Name & Validate
        $name = $args[0];
        if (!preg_match('/^[a-z_]+$/i', $name)) {
            Message::error("Invalid model name [{$name}].");
            return 1;
        }

        // Get Extension & Validate
        $ext = strtolower(Argument::getValue('ext', $args, 'twig'));

        if (empty($ext)) {
            Message::error("Extension couldn't be empty!");
            return 1;
        }

        if (!preg_match('/^html|twig$/i', $ext)) {
            Message::error("Invalid extension [{$ext}]. Accepted extensions are 'html' and 'twig'.");
            return 1;
        }

        // Get Path & Validate
        $tpl_dir = "{$basePath}/template";
        $path = trim(strtolower(Argument::getValue('path', $args, '')), '/');

        $tpl = "{$name}.{$ext}";

        if ($path) {
            if (!preg_match('/^[a-z_\-\/]+$/i', $path)) {
                Message::error("Invalid path [{$path}]. Example: admin/defaul.");
                return 1;
            }

            // Make Directory if Does Not Exists
            if (!Directory::exists("{$tpl_dir}/{$path}")) {
                $action = Argument::readline("Directory [{$path}] doesn't exists! Want to create?");

                if (!$action) {
                    Message::warning("Canceled by user.", 'canceled');
                    return 0;
                }

                // $tpl_dir = "{$tpl_dir}/{$path}";
                Directory::make("{$tpl_dir}/{$path}");
            }
            $tpl = "{$path}/{$tpl}";
        }

        $file = "{$tpl_dir}/$tpl";

        if (is_file($file)) {
            Message::info("[{$tpl}] already exists.");
            return 0;
        }

        try {
            $content = Stub::render('template', [
                'title' => basename($name),
            ]);

            Stub::write($file, $content);
            Message::success("Template created successfully.");
        } catch (\Throwable $th) {
            Message::error($th->getMessage());
            return 1;
        }
        return 0;
    }

    public function command(): string
    {
        return "php laika make:template <name> [--ext=twig --path=path]";
    }

    public function help(): array
    {
        return [
            'signature'     =>  $this->signature(),
            'description'   =>  'Create a new Twig template view',
            'command'       =>  $this->command(),
            'inputs'        =>  ['name' => 'Template name to make'],
            'params'        =>  []
        ];
    }
}
