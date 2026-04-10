# Localize for CodeIgniter 4

Locale management and URL localization filter for CodeIgniter 4.

This package adds a reusable localization filter that can:

- detect the visitor locale
- redirect root requests like `/` to `/{locale}`
- redirect requests without locale prefix to `/{locale}/...`
- persist the active locale in session and cookie
- validate locale segments against `Config\App::$supportedLocales`

## Installation

```bash
composer require domprojects/codeigniter4-localize
```

## How It Works

The package registers a `localize` filter automatically through a `Registrar`.

It does not modify `app/Config/Filters.php` manually.

## Configuration

The package uses two configuration sources:

1. `Config\App`
2. `domProjects\CodeIgniterLocalize\Config\Localize`

You should configure your project locales in `app/Config/App.php`:

```php
public string $defaultLocale = 'en';
public array $supportedLocales = ['en', 'fr'];
```

Optional package behavior can be customized by creating:

```text
app/Config/Localize.php
```

Example:

```php
<?php

namespace Config;

use domProjects\CodeIgniterLocalize\Config\Localize as BaseLocalize;

class Localize extends BaseLocalize
{
    public bool $redirectRoot = true;
    public bool $redirectMissingLocale = true;
    public string $invalidLocaleBehavior = '404';

    public array $excluded = [
        'api/*',
        'assets/*',
        'favicon.ico',
        'robots.txt',
    ];
}
```

## Recommended Behavior

Locale priority:

1. locale found in URL
2. locale stored in session
3. locale stored in cookie
4. browser language
5. `Config\App::$defaultLocale`

Recommended request behavior:

- `/` redirects to `/{locale}`
- `/about` redirects to `/{locale}/about`
- `/fr/about` applies `fr`
- `/zz/about` returns `404` when `invalidLocaleBehavior = '404'`
- assets and technical paths stay excluded from localization

## Routes

For locale-prefixed URLs, a route group like this is recommended:

```php
$routes->useSupportedLocalesOnly(true);

$routes->group('{locale}', static function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('about', 'Pages::about');
});
```

If your application does not use locale prefixes in routes, you can disable automatic redirection in the package config.

## Package Structure

```text
src/
  Config/
    Localize.php
    Registrar.php
  Filters/
    Localize.php
```

## License

MIT
