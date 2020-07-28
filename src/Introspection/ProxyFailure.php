<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use EcPhp\CasLib\Introspection\Contract\ProxyFailure as ProxyFailureInterface;

final class ProxyFailure extends Introspection implements ProxyFailureInterface
{
    public function getMessage(): string
    {
        return $this->getParsedResponse()['serviceResponse']['proxyFailure'];
    }
}
