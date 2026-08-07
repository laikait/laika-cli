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
laika model:make User
```
The global binary detects the current project by walking up from your
working directory until it finds `lf-boot/app.php` — no `php` prefix needed.

## Usage
```bash
php laika route:make users
php laika pipeline:make Auth
php laika filter:make Log
php laika model:make User --table=users --id=id --uid=uid
php laika template:make admin/dashboard
php laika service:make --name=Mailer --class=App\\Model\\MailerModel
php laika controller:make UserController

php laika route:list
php laika pipeline:list
php laika model:list

php laika model:remove User

php laika model:rename --old=User --new=Customer
```
