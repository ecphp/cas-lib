<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

interface ProxyFailure extends IntrospectionInterface
{
    public function getMessage(): string;
}
