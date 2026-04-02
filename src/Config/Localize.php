<?php

namespace domProjects\CodeIgniterLocalize\Config;

use CodeIgniter\Config\BaseConfig;

class Localize extends BaseConfig
{
    public bool $enabled = true;
    public bool $detectFromBrowser = true;
    public bool $storeInSession = true;
    public bool $storeInCookie = true;
    public string $sessionKey = 'locale';
    public string $cookieName = 'locale';
    public int $cookieExpire = 31536000;
    public bool $redirectRoot = true;
    public bool $redirectMissingLocale = true;
    public string $invalidLocaleBehavior = '404';

    /**
     * @var list<string>
     */
    public array $excluded = [
        'api/*',
        'assets/*',
        'favicon.ico',
        'robots.txt',
        'sitemap.xml',
    ];
}
