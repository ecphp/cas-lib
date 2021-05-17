<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Contract;

interface ServiceValidate extends CasResponseInterface
{
    public function getCredentials(): array;

    public function getProxies(): array;
}
