<?php

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Handler\ProxyCallback;
use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Redirect\Login;
use EcPhp\CasLib\Redirect\Logout;
use EcPhp\CasLib\Service\Proxy;
use EcPhp\CasLib\Service\ProxyValidate;
use EcPhp\CasLib\Service\ServiceValidate;
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

final class Cas implements CasInterface
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
    public function handleProxyCallback(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $proxyCallback = new ProxyCallback(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $response ?? $proxyCallback->handle();
    }

    /**
     * {@inheritdoc}
     */
    public function login(array $parameters = []): ?ResponseInterface
    {
        $login = new Login(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $login->handle();
    }

    /**
     * {@inheritdoc}
     */
    public function logout(array $parameters = []): ?ResponseInterface
    {
        $logout = new Logout(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $logout->handle();
    }

    /**
     * {@inheritdoc}
     */
    public function requestProxyTicket(array $parameters = [], ?ResponseInterface $response = null): ?ResponseInterface
    {
        $proxyRequestService = new Proxy(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getRequestFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger(),
            $this->getIntrospector()
        );

        if (null === $response) {
            if (null === $response = $proxyRequestService->handle()) {
                $this
                    ->getLogger()
                    ->error('Error during the proxy ticket request.');

                return null;
            }
        }

        $credentials = $proxyRequestService->getCredentials($response);

        if (null === $credentials) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the user.');
        }

        return $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function requestProxyValidate(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $proxyValidateService = new ProxyValidate(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getRequestFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger(),
            $this->getIntrospector()
        );

        if (null === $response) {
            if (null === $response = $proxyValidateService->handle()) {
                $this
                    ->getLogger()
                    ->error('Error during the proxy validate request.');

                return null;
            }
        }

        $credentials = $proxyValidateService->getCredentials($response);

        if (null === $credentials) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the user.');
        }

        return $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function requestServiceValidate(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $serviceValidateService = new ServiceValidate(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getRequestFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger(),
            $this->getIntrospector()
        );

        if (null === $response) {
            if (null === $response = $serviceValidateService->handle()) {
                $this
                    ->getLogger()
                    ->error('Error during the service validate request.');

                return null;
            }
        }

        $credentials = $serviceValidateService->getCredentials($response);

        if (null === $credentials) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the user.');
        }

        return $credentials;
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

    private function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    private function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    private function getIntrospector(): IntrospectorInterface
    {
        return $this->introspector;
    }

    private function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    private function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    private function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    private function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    private function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    private function proxyMode(): bool
    {
        return isset($this->getProperties()['protocol']['serviceValidate']['default_parameters']['pgtUrl']);
    }
}
