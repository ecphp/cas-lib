<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract\Response\Type;

use EcPhp\CasLib\Contract\Response\CasResponseInterface;

interface ServiceValidate extends CasResponseInterface
{
    public function getCredentials(): array;

    public function getProxies(): array;

    public function getProxyGrantingTicket(): string;
}
