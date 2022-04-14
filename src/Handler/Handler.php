<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Utils\Uri;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

abstract class Handler
{
    private CacheItemPoolInterface $cache;

    private LoggerInterface $logger;

    private array $parameters;

    private PropertiesInterface $properties;

    private ResponseFactoryInterface $responseFactory;

    private StreamFactoryInterface $streamFactory;

    private UriFactoryInterface $uriFactory;

    /**
     * @param array[]|string[] $parameters
     */
    public function __construct(
        array $parameters,
        PropertiesInterface $properties,
        UriFactoryInterface $uriFactory,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger
    ) {
        $this->parameters = $parameters;
        $this->properties = $properties;
        $this->uriFactory = $uriFactory;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param mixed[]|string[]|UriInterface[] $query
     */
    protected function buildUri(UriInterface $from, string $name, array $query = []): UriInterface
    {
        $properties = $this->getProperties();

        // Remove parameters that are not allowed.
        $query = array_intersect_key(
            $query,
            (array) array_combine(
                $properties['protocol'][$name]['allowed_parameters'] ?? [],
                $properties['protocol'][$name]['allowed_parameters'] ?? []
            )
        ) + Uri::getParams($from);

        $baseUrl = parse_url($properties['base_url']);

        if (false === $baseUrl) {
            $baseUrl = ['path' => ''];
            $properties['base_url'] = '';
        }

        $baseUrl += ['path' => ''];

        if (true === array_key_exists('service', $query)) {
            $query['service'] = (string) $query['service'];
        }

        // Filter out empty $query parameters
        $query = array_filter(
            $query,
            static function ($item): bool {
                if ([] === $item) {
                    return false;
                }

                return '' !== $item;
            }
        );

        return $this->getUriFactory()
            ->createUri($properties['base_url'])
            ->withPath($baseUrl['path'] . $properties['protocol'][$name]['path'])
            ->withQuery(http_build_query($query))
            ->withFragment($from->getFragment());
    }

    /**
     * @param array[]|bool[]|string[] $parameters
     *
     * @return string[]
     */
    protected function formatProtocolParameters(array $parameters): array
    {
        $parameters = array_filter(
            $parameters
        );

        $parameters = array_map(
            static function ($parameter) {
                return true === $parameter ? 'true' : $parameter;
            },
            $parameters
        );

        if (true === array_key_exists('service', $parameters)) {
            $service = $this->getUriFactory()->createUri(
                $parameters['service']
            );

            $service = Uri::removeParams(
                $service,
                'ticket'
            );

            $parameters['service'] = (string) $service;
        }

        return $parameters;
    }

    protected function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return array[]
     */
    protected function getParameters(RequestInterface $request): array
    {
        return $this->parameters + ($this->getProtocolProperties($request)['default_parameters'] ?? []);
    }

    protected function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    /**
     * Get the scoped properties of the protocol endpoint.
     *
     * @return array[]
     */
    protected function getProtocolProperties(RequestInterface $request): array
    {
        return [];
    }

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    protected function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }
}
