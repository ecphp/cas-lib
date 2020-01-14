<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

/**
 * Interface Proxy.
 */
interface Proxy extends IntrospectionInterface
{
    /**
     * @return string
     */
    public function getProxyTicket(): string;
}
