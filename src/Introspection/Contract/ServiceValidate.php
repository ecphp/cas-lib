<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

interface ServiceValidate extends IntrospectionInterface
{
    public function getCredentials(): array;

    public function getProxies(): array;
}
