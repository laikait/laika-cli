# Laika CLI

CLI generator for the Laika PHP MVC Framework.

## Install
```bash
composer require laikait/laika-cli
```

## Usage
```bash
php laika make:route users
php laika make:middleware Auth
php laika make:afterware Log
php laika make:model User --table=users --schema
php laika make:schema orders
php laika make:template admin/dashboard
php laika make:service Mailer
php laika make:controller UserController

php laika list:routes
php laika list:middleware
php laika list:models

php laika remove:model User
php laika remove:route users

php laika rename:model User Customer
php laika rename:route users clients
```

## Global install
```bash
composer global require laikait/laika-cli
laika make:model User
```
