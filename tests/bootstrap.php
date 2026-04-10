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

$packageRoot = realpath(__DIR__ . '/..');
$rootPath    = $packageRoot;
$packageMode = $packageRoot !== false && is_file($packageRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

if (! $packageMode) {
    $rootPath = realpath(__DIR__ . '/../../../../');
}

if ($rootPath === false || ! is_file($rootPath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    throw new RuntimeException('Unable to resolve the project root for localize tests.');
}

$publicPath = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'ci4-localize-test-public';

if (! is_dir($publicPath) && ! mkdir($publicPath, 0775, true) && ! is_dir($publicPath)) {
    throw new RuntimeException('Unable to create the test public directory for localize tests.');
}

if ($packageMode) {
    $testAppRoot  = $rootPath . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'test-app';
    $configPath   = $testAppRoot . DIRECTORY_SEPARATOR . 'Config';
    $writablePath = $rootPath . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'writable';
    $frameworkRoot = $rootPath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework';
    $pathsFile    = $configPath . DIRECTORY_SEPARATOR . 'Paths.php';

    if (! is_dir($configPath) && ! mkdir($configPath, 0775, true) && ! is_dir($configPath)) {
        throw new RuntimeException('Unable to create the test config directory for localize tests.');
    }

    foreach ([$writablePath, $writablePath . DIRECTORY_SEPARATOR . 'cache', $writablePath . DIRECTORY_SEPARATOR . 'logs', $writablePath . DIRECTORY_SEPARATOR . 'session', $writablePath . DIRECTORY_SEPARATOR . 'uploads'] as $directory) {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create the writable directory for localize tests.');
        }
    }

    $pathsContents = <<<PHP
<?php

namespace Config;

class Paths
{
    public string \$systemDirectory = %s;
    public string \$appDirectory = %s;
    public string \$writableDirectory = %s;
    public string \$testsDirectory = %s;
    public string \$viewDirectory = %s;
    public string \$envDirectory = %s;
}
PHP;

    $pathsContents = sprintf(
        $pathsContents,
        var_export(str_replace('\\', '/', $frameworkRoot . '/system'), true),
        var_export(str_replace('\\', '/', $frameworkRoot . '/app'), true),
        var_export(str_replace('\\', '/', $writablePath), true),
        var_export(str_replace('\\', '/', $rootPath . DIRECTORY_SEPARATOR . 'tests'), true),
        var_export(str_replace('\\', '/', $frameworkRoot . '/app/Views'), true),
        var_export(str_replace('\\', '/', $rootPath), true),
    );

    if (! is_file($pathsFile) || file_get_contents($pathsFile) !== $pathsContents) {
        file_put_contents($pathsFile, $pathsContents);
    }

    defined('CONFIGPATH') || define('CONFIGPATH', $configPath . DIRECTORY_SEPARATOR);
} else {
    defined('CONFIGPATH') || define('CONFIGPATH', $rootPath . DIRECTORY_SEPARATOR . 'app/Config' . DIRECTORY_SEPARATOR);
}

defined('HOMEPATH')    || define('HOMEPATH', $rootPath . DIRECTORY_SEPARATOR);
defined('PUBLICPATH')  || define('PUBLICPATH', $publicPath . DIRECTORY_SEPARATOR);
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
