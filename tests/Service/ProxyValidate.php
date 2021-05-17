<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace tests\EcPhp\CasLib\Service;

use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Service\Service;
use EcPhp\CasLib\Utils\Uri;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
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

    public function getRequest(): RequestInterface
    {
        return parent::getRequest();
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return parent::getRequestFactory();
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return parent::getResponseFactory();
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return parent::getServerRequest();
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return parent::getStreamFactory();
    }

    public function getUriFactory(): UriFactoryInterface
    {
        return parent::getUriFactory();
    }

    public function parse(ResponseInterface $response): array
    {
        return parent::parse($response);
    }

    public function updateParsedResponseWithPgt(array $response): ?array
    {
        return parent::updateParsedResponseWithPgt($response);
    }

    protected function getProtocolProperties(): array
    {
        $protocolProperties = $this->getProperties()['protocol']['proxyValidate'] ?? [];

        $protocolProperties['default_parameters'] += [
            'service' => (string) $this->getServerRequest()->getUri(),
            'ticket' => Uri::getParam($this->getServerRequest()->getUri(), 'ticket'),
        ];

        return $protocolProperties;
    }

    /**
     * Get the URI.
     */
    protected function getUri(): UriInterface
    {
        return $this->buildUri(
            $this->getServerRequest()->getUri(),
            'proxyValidate',
            $this->formatProtocolParameters($this->getParameters())
        );
    }
}
