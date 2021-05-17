<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\ProxyServiceValidate as ProxyServiceValidateInterface;

final class ProxyServiceValidate extends CasResponse implements ProxyServiceValidateInterface
{
    public function getCredentials(): array
    {
        return $this->parse()['serviceResponse']['authenticationSuccess'];
    }

    public function getProxies(): array
    {
        $hasProxy = isset($this->parse()['serviceResponse']['authenticationSuccess']['proxies']);

        return true === $hasProxy ?
            $this->parse()['serviceResponse']['authenticationSuccess']['proxies'] :
            [];
    }

}
