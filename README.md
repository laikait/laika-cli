# Laika CLI

CLI generator for the Laika PHP MVC Framework.

## Install

`laika-cli` ships as a dependency of `laikait/laika-core`, so every Laika
project already has it. If you started from `laikait/laika-framework`, the
wiring below is already in your `composer.json` and the executable is
generated for you on `composer install`, `update`, `dump-autoload` and
`create-project` — no manual step needed.

```bash
php laika help
```

### Wiring it up manually

Composer only runs scripts declared by the **root** project, never by a
dependency. So a project that wasn't created from the framework skeleton
needs to call the generator itself:

```json
"scripts": {
    "post-autoload-dump": [
        "Laika\\Cli\\ScriptHandler::generate"
    ],
    "post-create-project-cmd": [
        "Laika\\Cli\\ScriptHandler::generate"
    ]
}
```

Keep `ScriptHandler::generate` **first** in `post-autoload-dump` if later
entries in that list invoke `laika` themselves — it has to exist before they
run.

### What gets generated

| File | Platform | How you run it |
| --- | --- | --- |
| `laika` | all | `php laika help` — or `./laika help` on Linux/macOS |

One file, the same on every platform. It is a thin proxy into
`vendor/laikait/laika-cli`, so it always matches the version this project has
installed. It is rewritten only when its content actually changes, and
regenerates if you delete it.

> **Windows:** run it as `php laika ...`, not a bare `laika ...`. There is
> deliberately no `laika.bat` shim — cmd and PowerShell resolve commands
> through `PATHEXT` and will never execute an extensionless file. A `laika.bat`
> left over from an earlier version is deleted on the next `composer install`.
> If you want a bare `laika` command on Windows, use the global install below.

> Versions before 3.0 shipped this package as a Composer *plugin*, which
> required an `allow-plugins` entry in every consuming project. That is no
> longer needed — you can drop `"laikait/laika-cli": true` from your
> `config.allow-plugins`.

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

This is the one case where a bare `laika` does work on Windows: the shims in
Composer's global `vendor/bin` are built by Composer itself, not by this
package, and it still writes a `.bat` there.

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
