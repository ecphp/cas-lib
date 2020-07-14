<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

interface ServiceValidate extends IntrospectionInterface
{
    public function getCredentials(): array;

    public function getProxies(): array;
}
