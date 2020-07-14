<?php

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
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
     * @var \EcPhp\CasLib\Introspection\Contract\IntrospectorInterface
     */
    private $introspector;

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

    public function __construct(
        ServerRequestInterface $serverRequest,
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
        $this->serverRequest = $serverRequest;
        $this->properties = $properties;
        $this->client = $client;
        $this->uriFactory = $uriFactory;
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->introspector = $introspector;
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

        return $this->getIntrospector()->detect($response)->getParsedResponse();
    }

    public function detect(ResponseInterface $response): IntrospectionInterface
    {
        return $this->getIntrospector()->detect($response);
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
    public function supportAuthentication(array $parameters = []): bool
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

    protected function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    protected function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    protected function getIntrospector(): IntrospectorInterface
    {
        return $this->introspector;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    protected function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    protected function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    protected function proxyMode(): bool
    {
        return isset($this->getProperties()['protocol']['serviceValidate']['default_parameters']['pgtUrl']);
    }
}
