<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Configuration;

use ArrayAccess;

/**
 * Interface PropertiesInterface.
 *
 * @template-extends ArrayAccess<string, mixed>
 */
interface PropertiesInterface extends ArrayAccess
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array;
}
