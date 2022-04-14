<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\CasLib\Service;

use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Service\Service;
use EcPhp\CasLib\Utils\Uri;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

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

    public function getIntrospector(): IntrospectorInterface
    {
        return parent::getIntrospector();
    }

    public function getLogger(): LoggerInterface
    {
        return parent::getLogger();
    }

    public function parse(RequestInterface $request, ResponseInterface $response): array
    {
        return parent::parse($request, $response);
    }

    public function updateParsedResponseWithPgt(array $response): ?array
    {
        return parent::updateParsedResponseWithPgt($response);
    }

    protected function getProtocolProperties(RequestInterface $request): array
    {
        $protocolProperties = $this->getProperties()['protocol']['proxyValidate'] ?? [];

        $protocolProperties['default_parameters'] += [
            'service' => (string) $request->getUri(),
            'ticket' => Uri::getParam($request->getUri(), 'ticket'),
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
