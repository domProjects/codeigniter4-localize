<?php

/**
 * This file is part of domprojects/codeigniter4-localize.
 *
 * (c) domProjects
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace domProjects\CodeIgniterLocalize\Config;

use domProjects\CodeIgniterLocalize\Filters\Localize;

class Registrar
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function Filters(): array
    {
        return [
            'aliases' => [
                'localize' => Localize::class,
            ],
            'filters' => [
                'localize' => [
                    'before' => ['*'],
                ],
            ],
        ];
    }
}
