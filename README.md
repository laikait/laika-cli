# Laika CLI

CLI generator for the Laika PHP MVC Framework.

## Install

`laika-cli` ships as a dependency of `laikait/laika-core`, so every Laika
project already has it. The first time Composer builds the autoloader
(`composer install`/`update`, or `create-project`), a `laika` executable is
generated automatically in your project root — no manual step needed.

```bash
php laika help
```

> Composer 2.2+ requires explicit trust for packages that ship a plugin. If
> you see a warning about `laikait/laika-cli` not being allowed to run code,
> add it to your project's `composer.json`:
> ```json
> "config": {
>     "allow-plugins": {
>         "laikait/laika-cli": true
>     }
> }
> ```
> (Already set up for you if you started from `laikait/laika-framework`.)

## Global install
Prefer a single `laika` command available in every project? Install it
globally instead:
```bash
composer global require laikait/laika-cli
```
Make sure Composer's global `vendor/bin` directory is on your `PATH` (see the
[Composer docs](https://getcomposer.org/doc/03-cli.md#global)), then run
`laika` from inside any Laika project directory (or a sub-directory of one):
```bash
laika make:model User
```
The global binary detects the current project by walking up from your
working directory until it finds `lf-boot/app.php` — no `php` prefix needed.

## Usage
```bash
php laika make:route users
php laika make:pipeline Auth
php laika make:filter Log
php laika make:model User --table=users --id=id --uid=uid
php laika make:schema orders
php laika make:template admin/dashboard
php laika make:service Mailer
php laika make:controller UserController

php laika list:routes
php laika list:pipeline
php laika list:models

php laika remove:model User
php laika remove:route users

php laika rename:model User Customer
php laika rename:route users clients
```
