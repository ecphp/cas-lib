<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\Proxy as ProxyInterface;

final class Proxy extends CasResponse implements ProxyInterface
{
    public function getProxyTicket(): string
    {
        return $this->parse()['serviceResponse']['proxySuccess']['proxyTicket'];
    }
}
