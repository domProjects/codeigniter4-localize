<?php

declare(strict_types=1);

/**
 * This file is part of domprojects/codeigniter4-localize.
 *
 * (c) domProjects
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$rootPath = realpath(__DIR__ . '/../../../../');

if ($rootPath === false) {
    throw new RuntimeException('Unable to resolve the project root for localize tests.');
}

defined('HOMEPATH')    || define('HOMEPATH', $rootPath . DIRECTORY_SEPARATOR);
defined('CONFIGPATH')  || define('CONFIGPATH', $rootPath . DIRECTORY_SEPARATOR . 'app/Config' . DIRECTORY_SEPARATOR);
defined('PUBLICPATH')  || define('PUBLICPATH', $rootPath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
defined('TESTPATH')    || define('TESTPATH', __DIR__ . DIRECTORY_SEPARATOR);
defined('SUPPORTPATH') || define('SUPPORTPATH', __DIR__ . DIRECTORY_SEPARATOR . '_support' . DIRECTORY_SEPARATOR);

spl_autoload_register(static function (string $class): void {
    $prefix = 'domProjects\\CodeIgniterLocalize\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file         = __DIR__ . '/../src/' . $relativePath . '.php';

    if (is_file($file)) {
        require_once $file;
    }
}, true, true);

require HOMEPATH . 'vendor/autoload.php';
require HOMEPATH . 'vendor/codeigniter4/framework/system/Test/bootstrap.php';
