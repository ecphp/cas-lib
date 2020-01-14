<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

/**
 * Interface ServiceValidate.
 */
interface ServiceValidate extends IntrospectionInterface
{
    /**
     * @return array[]
     */
    public function getCredentials(): array;

    /**
     * @return array[]
     */
    public function getProxies(): array;
}
