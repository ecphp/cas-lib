<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use EcPhp\CasLib\Contract\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Exception\CasHandlerException;
use EcPhp\CasLib\Utils\Uri;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;
use function is_string;
use function sprintf;

abstract class Handler
{
    /**
     * @param array[]|string[] $parameters
     */
    public function __construct(
        private readonly array $parameters,
        private readonly CacheItemPoolInterface $cache,
        private readonly CasResponseBuilderInterface $casResponseBuilder,
        private readonly ClientInterface $client,
        private readonly PropertiesInterface $properties,
        private readonly Psr17Interface $psr17
    ) {}

    /**
     * This function will aggregate all the input arrays into a single array.
     *
     * The rule of concatenation is that the previous array will have precedence
     * over the current array.
     *
     * Therefore: buildParameters([a=>1], [a=>2,b=>3]) will return [a=>1, b=>3]
     *
     * @param array<array-key, mixed> ...$parameters
     *
     * @return array<array-key, mixed>
     */
    protected function buildParameters(array ...$parameters): array
    {
        return $this->formatParameters(
            array_reduce(
                $parameters,
                static fn (array $carry, array $item): array => $carry + $item,
                []
            )
        );
    }

    protected function buildUri(
        UriInterface $from,
        string $type,
        array $queryParams = []
    ): UriInterface {
        $properties = $this->getProperties()->jsonSerialize();

        $queryParams += Uri::getParams($from);
        $baseUrl = parse_url((string) $properties['base_url']);

        if (false === $baseUrl) {
            throw new CasHandlerException(
                sprintf('Unable to parse URL: %s', $properties['base_url'])
            );
        }

        if (true === array_key_exists('service', $queryParams)) {
            $queryParams['service'] = (string) $queryParams['service'];
        }

        return $this
            ->getPsr17()
            ->createUri($properties['base_url'])
            ->withPath(sprintf('%s%s', $baseUrl['path'] ?? '', $properties['protocol'][$type]['path']))
            ->withQuery(http_build_query($queryParams))
            ->withFragment($from->getFragment());
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

    protected function getParameters(): array
    {
        return $this->parameters;
    }

    protected function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    protected function getPsr17(): Psr17Interface
    {
        return $this->psr17;
    }

    /**
     * @param array[]|bool[]|string[] $parameters
     *
     * @return string[]|array[]
     */
    private function formatParameters(array $parameters): array
    {
        $parameters = array_map(
            static fn ($parameter) => true === $parameter ? 'true' : $parameter,
            array_filter($parameters)
        );

        if (true === array_key_exists('service', $parameters) && is_string($parameters['service'])) {
            $parameters['service'] = (string) Uri::removeParams(
                $this
                    ->getPsr17()
                    ->createUri($parameters['service']),
                'ticket'
            );
        }

        ksort($parameters);

        return $parameters;
    }
}
