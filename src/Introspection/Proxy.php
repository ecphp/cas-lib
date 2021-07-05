<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use EcPhp\CasLib\Introspection\Contract\Proxy as ProxyInterface;

/**
 * Class Proxy.
 */
final class Proxy extends Introspection implements ProxyInterface
{
    public function getProxyTicket(): string
    {
        return $this->getParsedResponse()['serviceResponse']['proxySuccess']['proxyTicket'];
    }
}
