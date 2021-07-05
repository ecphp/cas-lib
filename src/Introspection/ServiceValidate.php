<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

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
