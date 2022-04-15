<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\CasLib\Handler;

use EcPhp\CasLib\Handler\Service;
use EcPhp\CasLib\Utils\Uri;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class ProxyValidate extends Service
{
    public function getCache(): CacheItemPoolInterface
    {
        return parent::getCache();
    }

    public function getClient(): ClientInterface
    {
        return parent::getClient();
    }

    protected function getProtocolProperties(UriInterface $uri): array
    {
        $protocolProperties = $this->getProperties()['protocol']['proxyValidate'] ?? [];

        $protocolProperties['default_parameters'] += [
            'service' => (string) $uri,
            'ticket' => Uri::getParam($uri, 'ticket'),
        ];

        return $protocolProperties;
    }

    protected function getUri(RequestInterface $request): UriInterface
    {
        return $this->buildUri(
            $request->getUri(),
            'proxyValidate',
            $this->formatProtocolParameters($this->getParameters($request))
        );
    }
}
