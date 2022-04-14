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
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Utils\Uri;
use Exception;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;

use const JSON_ERROR_NONE;

abstract class Handler
{
    private CacheItemPoolInterface $cache;

    private ClientInterface $client;

    private IntrospectorInterface $introspector;

    private array $parameters;

    private PropertiesInterface $properties;

    private Psr17Interface $psr17;

    /**
     * @param array[]|string[] $parameters
     */
    public function __construct(
        array $parameters,
        CacheItemPoolInterface $cache,
        ClientInterface $client,
        IntrospectorInterface $introspector,
        PropertiesInterface $properties,
        Psr17Interface $psr17
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->introspector = $introspector;
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

    protected function getClient(): ClientInterface
    {
        return $this->client;
    }

    protected function getIntrospector(): IntrospectorInterface
    {
        return $this->introspector;
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

    protected function getPsr17(): Psr17Interface
    {
        return $this->psr17;
    }

    /**
     * Normalize a response.
     */
    protected function normalize(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->parse($request, $response);

        if ([] === $body) {
            throw new Exception('Unable to parse the response during the normalization process.');
        }

        $body = json_encode($body);

        if (false === $body || JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception('Unable to encode the response in JSON during the normalization process.');
        }

        return $response
            ->withBody($this->getPsr17()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Parse the response format.
     *
     * @return array[]|string[]
     *   The parsed response.
     */
    protected function parse(RequestInterface $request, ResponseInterface $response): array
    {
        return $this
            ->getIntrospector()
            ->parse(
                $response,
                $this->getProtocolProperties($request)['default_parameters']['format'] ?? 'XML'
            );
    }
}
