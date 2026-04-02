<?php

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
