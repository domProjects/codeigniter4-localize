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

namespace domProjects\CodeIgniterLocalize\Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use domProjects\CodeIgniterLocalize\Config\Registrar;
use domProjects\CodeIgniterLocalize\Filters\Localize;

/**
 * @internal
 */
final class RegistrarTest extends CIUnitTestCase
{
    public function testItRegistersAliasAndGlobalFilter(): void
    {
        $filters = Registrar::Filters();

        $this->assertSame(Localize::class, $filters['aliases']['localize']);
        $this->assertSame(['*'], $filters['filters']['localize']['before']);
    }
}
