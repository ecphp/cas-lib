<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Type;

use EcPhp\CasLib\Contract\Response\Type\Proxy as ProxyInterface;

final class Proxy extends CasResponse implements ProxyInterface
{
    public function getProxyTicket(): string
    {
        return $this->toArray()['serviceResponse']['proxySuccess']['proxyTicket'];
    }
}
