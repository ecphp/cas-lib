<?php

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Introspection\Introspector;
use EcPhp\CasLib\Utils\Uri;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

/**
 * Class AbstractCas.
 */
abstract class AbstractCas implements CasInterface
{
    /**
     * The cache.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * The HTTP client.
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    private $client;

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * The CAS properties.
     *
     * @var PropertiesInterface
     */
    private $properties;

    /**
     * The request factory.
     *
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * The response factory.
     *
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * The server request.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $serverRequest;

    /**
     * The stream factory.
     *
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * The URI factory.
     *
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    private $uriFactory;

    /**
     * AbstractCas constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \EcPhp\CasLib\Configuration\PropertiesInterface $properties
     * @param \Psr\Http\Client\ClientInterface $client
     * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ServerRequestInterface $serverRequest,
        PropertiesInterface $properties,
        ClientInterface $client,
        UriFactoryInterface $uriFactory,
        ResponseFactoryInterface $responseFactory,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger
    ) {
        $this->serverRequest = $serverRequest;
        $this->properties = $properties;
        $this->client = $client;
        $this->uriFactory = $uriFactory;
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $parameters = []): ?array
    {
        if (null === $response = $this->requestTicketValidation($parameters)) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the request.');

            return null;
        }

        return Introspector::detect($response)
            ->getParsedResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function requestTicketValidation(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        if (false === $this->supportAuthentication($parameters)) {
            return null;
        }

        /** @var string $ticket */
        $ticket = Uri::getParam(
            $this->getServerRequest()->getUri(),
            'ticket',
            ''
        );

        $parameters += ['ticket' => $ticket];

        return true === $this->proxyMode() ?
            $this->requestProxyValidate($parameters, $response) :
            $this->requestServiceValidate($parameters, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function supportAuthentication($parameters = []): bool
    {
        return array_key_exists('ticket', $parameters) || Uri::hasParams($this->getServerRequest()->getUri(), 'ticket');
    }

    /**
     * {@inheritdoc}
     */
    public function withServerRequest(ServerRequestInterface $serverRequest): CasInterface
    {
        $clone = clone $this;
        $clone->serverRequest = $serverRequest;

        return $clone;
    }

    /**
     * Get the cache.
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     *   The cache.
     */
    protected function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * Get the HTTP client.
     *
     * @return \Psr\Http\Client\ClientInterface
     *   The HTTP client.
     */
    protected function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Get the logger.
     *
     * @return \Psr\Log\LoggerInterface
     *   The logger.
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get the request factory.
     *
     * @return \Psr\Http\Message\RequestFactoryInterface
     *   The request factory.
     */
    protected function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Get the response factory.
     *
     * @return \Psr\Http\Message\ResponseFactoryInterface
     *   The response factory.
     */
    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * Get the server request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     *   The server request.
     */
    protected function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    /**
     * Get the stream factory.
     *
     * @return \Psr\Http\Message\StreamFactoryInterface
     *   The stream factory.
     */
    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * Get the URI factory.
     *
     * @return \Psr\Http\Message\UriFactoryInterface
     *   The URI factory.
     */
    protected function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    /**
     * @return bool
     */
    protected function proxyMode(): bool
    {
        return isset($this->getProperties()['protocol']['serviceValidate']['default_parameters']['pgtUrl']);
    }
}
