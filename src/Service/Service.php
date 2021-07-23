<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Handler\Handler;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Introspection\Contract\ServiceValidate;
use InvalidArgumentException;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

use const JSON_ERROR_NONE;

abstract class Service extends Handler
{
    private ClientInterface $client;

    private IntrospectorInterface $introspector;

    public function __construct(
        ServerRequestInterface $serverRequest,
        array $parameters,
        PropertiesInterface $properties,
        ClientInterface $client,
        Psr17Interface $psr17,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        IntrospectorInterface $introspector
    ) {
        parent::__construct(
            $serverRequest,
            $parameters,
            $properties,
            $psr17,
            $cache,
            $logger
        );

        $this->client = $client;
        $this->introspector = $introspector;
    }

    public function getCredentials(ResponseInterface $response): ?ResponseInterface
    {
        try {
            $introspect = $this->getIntrospector()->detect($response);
        } catch (InvalidArgumentException $exception) {
            $this
                ->getLogger()
                ->error($exception->getMessage());

            return null;
        }

        if (false === ($introspect instanceof ServiceValidate)) {
            $this
                ->getLogger()
                ->error(
                    'Service validation failed.',
                    [
                        'response' => (string) $response->getBody(),
                    ]
                );

            return null;
        }

        $parsedResponse = $introspect->getParsedResponse();
        $proxyGrantingTicket = array_key_exists(
            'proxyGrantingTicket',
            $parsedResponse['serviceResponse']['authenticationSuccess']
        );

        if (false === $proxyGrantingTicket) {
            $this
                ->getLogger()
                ->debug('Service validation service successful.');

            return $response->withHeader('Content-Type', 'application/json');
        }

        $parsedResponse = $this->updateParsedResponseWithPgt($parsedResponse);

        if (null === $parsedResponse) {
            return null;
        }

        $body = json_encode($parsedResponse);

        if (false === $body) {
            return null;
        }

        $this
            ->getLogger()
            ->debug('Proxy validation service successful.');

        return $response
            ->withBody($this->getPsr17()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }

    public function handle(): ?ResponseInterface
    {
        try {
            $response = $this->getClient()->sendRequest($this->getRequest());
        } catch (ClientExceptionInterface $exception) {
            $this
                ->getLogger()
                ->error($exception->getMessage());

            $response = null;
        }

        return null === $response ? $response : $this->normalize($response);
    }

    protected function getClient(): ClientInterface
    {
        return $this->client;
    }

    protected function getIntrospector(): IntrospectorInterface
    {
        return $this->introspector;
    }

    protected function getRequest(): RequestInterface
    {
        return $this->getPsr17()->createRequest('GET', $this->getUri());
    }

    /**
     * Get the URI.
     */
    abstract protected function getUri(): UriInterface;

    /**
     * Parse the response format.
     *
     * @return array[]|string[]
     *   The parsed response.
     */
    protected function parse(ResponseInterface $response): array
    {
        $format = $this->getProtocolProperties()['default_parameters']['format'] ?? 'XML';

        try {
            $array = $this->getIntrospector()->parse($response, $format);
        } catch (InvalidArgumentException $exception) {
            $this
                ->getLogger()
                ->error(
                    'Unable to parse the response with the specified format {format}.',
                    [
                        'format' => $format,
                        'response' => (string) $response->getBody(),
                    ]
                );

            $array = [];
        }

        return $array;
    }

    /**
     * @param array[] $response
     *
     * @return array[]|null
     */
    protected function updateParsedResponseWithPgt(array $response): ?array
    {
        $pgt = $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

        $hasPgtIou = $this->getCache()->hasItem($pgt);

        if (false === $hasPgtIou) {
            $this
                ->getLogger()
                ->error('CAS validation failed: pgtIou not found in the cache.', ['pgtIou' => $pgt]);

            return null;
        }

        $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'] = $this
            ->getCache()
            ->getItem($pgt)
            ->get();

        return $response;
    }

    /**
     * Normalize a response.
     */
    private function normalize(ResponseInterface $response): ResponseInterface
    {
        $body = $this->parse($response);

        if ([] === $body) {
            $this
                ->getLogger()
                ->error(
                    'Unable to parse the response during the normalization process.',
                    [
                        'body' => (string) $response->getBody(),
                    ]
                );

            return $response;
        }

        $body = json_encode($body);

        if (false === $body || JSON_ERROR_NONE !== json_last_error()) {
            $this
                ->getLogger()
                ->error(
                    'Unable to encode the response in JSON during the normalization process.',
                    [
                        'body' => (string) $response->getBody(),
                    ]
                );

            return $response;
        }

        $this
            ->getLogger()
            ->debug('Response normalization succeeded.', ['body' => $body]);

        return $response
            ->withBody($this->getPsr17()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }
}
