<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\ServiceValidate as ServiceValidateInterface;

final class ServiceValidate extends CasResponse implements ServiceValidateInterface
{
    public function getCredentials(): array
    {
        return $this->toArray()['serviceResponse']['authenticationSuccess'];
    }

    public function getProxies(): array
    {
        $toArray = $this->toArray();
        $toArray['serviceResponse']['authenticationSuccess'] += ['proxies' => []];

        return $toArray['serviceResponse']['authenticationSuccess']['proxies'];
    }

    public function isFailure(): bool
    {
        return isset($this->toArray()['serviceResponse']['authenticationFailure']);
    }
}
