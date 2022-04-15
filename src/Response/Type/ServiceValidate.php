<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Type;

use EcPhp\CasLib\Contract\Response\Type\ServiceValidate as ServiceValidateInterface;
use Exception;

use function array_key_exists;

final class ServiceValidate extends CasResponse implements ServiceValidateInterface
{
    public function getCredentials(): array
    {
        return $this->toArray()['serviceResponse']['authenticationSuccess'];
    }

    public function getProxies(): array
    {
        $hasProxy = isset($this->toArray()['serviceResponse']['authenticationSuccess']['proxies']);

        return true === $hasProxy ?
            $this->toArray()['serviceResponse']['authenticationSuccess']['proxies'] :
            [];
    }

    public function getProxyGrantingTicket(): string
    {
        $credentials = $this->getCredentials();

        $proxyGrantingTicket = array_key_exists(
            'proxyGrantingTicket',
            $credentials
        );

        if (false === $proxyGrantingTicket) {
            throw new Exception('Missing PGT in ServiceValidate Response.');
        }

        return $credentials['proxyGrantingTicket'];
    }
}
