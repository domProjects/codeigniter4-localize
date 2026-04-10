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

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_82,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->parallel();
    $rectorConfig->cacheClass(FileCacheStorage::class);

    if (is_dir('/tmp')) {
        $rectorConfig->cacheDirectory('/tmp/rector');
    }

    $rectorConfig->paths([
        __DIR__ . '/src/',
        __DIR__ . '/tests/',
    ]);

    $rectorConfig->autoloadPaths([
        __DIR__ . '/../../../vendor/autoload.php',
    ]);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/tests/bootstrap.php',
    ]);

    if (is_file(__DIR__ . '/phpstan.neon.dist')) {
        $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon.dist');
    }

    $rectorConfig->phpVersion(PhpVersion::PHP_82);
    $rectorConfig->importNames();

    $rectorConfig->skip([
        __DIR__ . '/tests/_support',
    ]);
};
