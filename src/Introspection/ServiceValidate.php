<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use EcPhp\CasLib\Introspection\Contract\ServiceValidate as ServiceValidateInterface;

final class ServiceValidate extends Introspection implements ServiceValidateInterface
{
    public function getCredentials(): array
    {
        return $this->getParsedResponse()['serviceResponse']['authenticationSuccess'];
    }

    public function getProxies(): array
    {
        $hasProxy = isset($this->getParsedResponse()['serviceResponse']['authenticationSuccess']['proxies']);

        return true === $hasProxy ?
            $this->getParsedResponse()['serviceResponse']['authenticationSuccess']['proxies'] :
            [];
    }
}
