<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Handler\Handler;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Introspection\Contract\ServiceValidate;
use Exception;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

use const JSON_ERROR_NONE;

abstract class Service extends Handler
{
    /**
     * @var \Psr\Http\Client\ClientInterface
     */
    private $client;

    /**
     * @var \EcPhp\CasLib\Introspection\Contract\IntrospectorInterface
     */
    private $introspector;

    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(
        array $parameters,
        PropertiesInterface $properties,
        ClientInterface $client,
        UriFactoryInterface $uriFactory,
        ResponseFactoryInterface $responseFactory,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        IntrospectorInterface $introspector
    ) {
        parent::__construct(
            $parameters,
            $properties,
            $uriFactory,
            $responseFactory,
            $streamFactory,
            $cache,
            $logger
        );

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->introspector = $introspector;
    }

    public function getCredentials(ResponseInterface $response): ?ResponseInterface
    {
        $introspect = $this->getIntrospector()->detect($response);

        if (false === ($introspect instanceof ServiceValidate)) {
            throw new Exception('Service validation failed');
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
            throw new Exception('Unable to update response with PGT.');
        }

        $body = json_encode($parsedResponse);

        if (false === $body) {
            throw new Exception('Unable to encode response.');
        }

        $this
            ->getLogger()
            ->debug('Proxy validation service successful.');

        return $response
            ->withBody($this->getStreamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }

    protected function getClient(): ClientInterface
    {
        return $this->client;
    }

    protected function getIntrospector(): IntrospectorInterface
    {
        return $this->introspector;
    }

    protected function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Normalize a response.
     */
    protected function normalize(ResponseInterface $response, string $format): ResponseInterface
    {
        $body = $this->parse($response, $format);

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
            ->withBody($this->getStreamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Parse the response format.
     *
     * @return array[]|string[]
     *   The parsed response.
     */
    protected function parse(ResponseInterface $response, string $format): array
    {
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

    protected function updateParsedResponseWithPgt(array $response): array
    {
        $pgt = $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

        $hasPgtIou = $this->getCache()->hasItem($pgt);

        if (false === $hasPgtIou) {
            throw new Exception('CAS validation failed: pgtIou not found in the cache.');
        }

        $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'] = $this
            ->getCache()
            ->getItem($pgt)
            ->get();

        return $response;
    }
}
