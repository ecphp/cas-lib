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
use EcPhp\CasLib\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Utils\Uri;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;

abstract class Handler
{
    private CacheItemPoolInterface $cache;

    private CasResponseBuilderInterface $casResponseBuilder;

    private ClientInterface $client;

    private array $parameters;

    private PropertiesInterface $properties;

    private Psr17Interface $psr17;

    /**
     * @param array[]|string[] $parameters
     */
    public function __construct(
        array $parameters,
        CacheItemPoolInterface $cache,
        CasResponseBuilderInterface $casResponseBuilder,
        ClientInterface $client,
        PropertiesInterface $properties,
        Psr17Interface $psr17
    ) {
        $this->cache = $cache;
        $this->casResponseBuilder = $casResponseBuilder;
        $this->client = $client;
        $this->parameters = $parameters;
        $this->properties = $properties;
        $this->psr17 = $psr17;
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
            static fn ($item): bool => [] === $item ? false : ('' !== $item)
        );

        return $this->getPsr17()
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
        $parameters = array_map(
            static fn ($parameter) => true === $parameter ? 'true' : $parameter,
            array_filter($parameters)
        );

        if (true === array_key_exists('service', $parameters)) {
            $service = $this->getPsr17()->createUri(
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

    protected function getCasResponseBuilder(): CasResponseBuilderInterface
    {
        return $this->casResponseBuilder;
    }

    protected function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @return array[]
     */
    protected function getParameters(RequestInterface $request): array
    {
        return $this->parameters + ($this->getProtocolProperties($request->getUri())['default_parameters'] ?? []);
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
    protected function getProtocolProperties(UriInterface $uri): array
    {
        return [];
    }

    protected function getPsr17(): Psr17Interface
    {
        return $this->psr17;
    }
}
