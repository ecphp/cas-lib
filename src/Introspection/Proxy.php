<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use EcPhp\CasLib\Introspection\Contract\Proxy as ProxyInterface;

/**
 * Class Proxy.
 */
final class Proxy extends Introspection implements ProxyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProxyTicket(): string
    {
        return $this->getParsedResponse()['serviceResponse']['proxySuccess']['proxyTicket'];
    }
}
