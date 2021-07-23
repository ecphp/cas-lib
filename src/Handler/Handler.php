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
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

/**
 * Class Handler.
 */
abstract class Handler
{
    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array[]|string[]
     */
    private $parameters;

    /**
     * @var \EcPhp\CasLib\Configuration\PropertiesInterface
     */
    private $properties;

    /**
     * @var Psr17Interface
     */
    private $psr17;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $serverRequest;

    /**
     * Handler constructor.
     *
     * @param array[]|string[] $parameters
     */
    public function __construct(
        ServerRequestInterface $serverRequest,
        array $parameters,
        PropertiesInterface $properties,
        Psr17Interface $psr17,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger
    ) {
        $this->serverRequest = $serverRequest;
        $this->parameters = $parameters;
        $this->properties = $properties;
        $this->psr17 = $psr17;
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

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return array[]
     */
    protected function getParameters(): array
    {
        return $this->parameters + ($this->getProtocolProperties()['default_parameters'] ?? []);
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
    protected function getProtocolProperties(): array
    {
        return [];
    }

    protected function getPsr17(): Psr17Interface
    {
        return $this->psr17;
    }

    protected function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }
}
