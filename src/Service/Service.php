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
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Handler\Handler;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Introspection\Contract\ServiceValidate;
use Exception;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;

use const JSON_ERROR_NONE;

abstract class Service extends Handler
{
    private ClientInterface $client;

    private IntrospectorInterface $introspector;

    public function __construct(
        array $parameters,
        PropertiesInterface $properties,
        ClientInterface $client,
        Psr17Interface $psr17,
        CacheItemPoolInterface $cache,
        IntrospectorInterface $introspector
    ) {
        parent::__construct(
            $parameters,
            $properties,
            $psr17,
            $cache
        );

        $this->client = $client;
        $this->introspector = $introspector;
    }

    public function getCredentials(ResponseInterface $response): ResponseInterface
    {
        $introspect = $this->getIntrospector()->detect($response);

        if (false === ($introspect instanceof ServiceValidate)) {
            throw new Exception('CAS Service validation failed.');
        }

        $parsedResponse = $introspect->getParsedResponse();
        $proxyGrantingTicket = array_key_exists(
            'proxyGrantingTicket',
            $parsedResponse['serviceResponse']['authenticationSuccess']
        );

        if (false === $proxyGrantingTicket) {
            return $response->withHeader('Content-Type', 'application/json');
        }

        $body = json_encode(
            $this->updateParsedResponseWithPgt($parsedResponse)
        );

        if (false === $body) {
            throw new Exception('Unable to JSON encode the body');
        }

        return $response
            ->withBody($this->getPsr17()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->getClient()->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        return $this->normalize($request, $response);
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
     * Get the URI.
     */
    abstract protected function getUri(RequestInterface $request): UriInterface;

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

    /**
     * @param array[] $response
     *
     * @return array[]|null
     */
    protected function updateParsedResponseWithPgt(array $response): array
    {
        $pgt = $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

        $hasPgtIou = $this
            ->getCache()
            ->hasItem($pgt);

        if (false === $hasPgtIou) {
            throw new Exception('CAS validation failed: pgtIou not found in the cache.');
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
    private function normalize(RequestInterface $request, ResponseInterface $response): ResponseInterface
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
}
