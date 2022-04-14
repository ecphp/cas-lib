<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

// phpcs:disable Generic.Files.LineLength.TooLong

final class Cas implements CasInterface
{
    private CacheItemPoolInterface $cache;

    private ClientInterface $client;

    private IntrospectorInterface $introspector;

    private LoggerInterface $logger;

    private PropertiesInterface $properties;

    private RequestFactoryInterface $requestFactory;

    private ResponseFactoryInterface $responseFactory;

    private StreamFactoryInterface $streamFactory;

    private UriFactoryInterface $uriFactory;

    public function __construct(
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

    public function authenticate(RequestInterface $request, array $parameters = []): ?array
    {
        if (null === $response = $this->requestTicketValidation($request, $parameters)) {
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

    public function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    public function handleProxyCallback(
        RequestInterface $request,
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $proxyCallback = new ProxyCallback(
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $response ?? $proxyCallback->handle($request);
    }

    public function login(RequestInterface $request, array $parameters = []): ?ResponseInterface
    {
        $login = new Login(
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $login->handle($request);
    }

    public function logout(RequestInterface $request, array $parameters = []): ?ResponseInterface
    {
        $logout = new Logout(
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $logout->handle($request);
    }

    public function requestProxyTicket(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface {
        $proxyRequestService = new Proxy(
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

        if (null === $response = $proxyRequestService->handle($request)) {
            $this
                ->getLogger()
                ->error('Error during the proxy ticket request.');

            return null;
        }

        $credentials = $proxyRequestService->getCredentials($response);

        if (null === $credentials) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the user.');
        }

        return $credentials;
    }

    public function requestProxyValidate(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface {
        $proxyValidateService = new ProxyValidate(
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

        if (null === $response = $proxyValidateService->handle($request)) {
            $this
                ->getLogger()
                ->error('Error during the proxy validate request.');

            return null;
        }

        $credentials = $proxyValidateService->getCredentials($response);

        if (null === $credentials) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the user.');
        }

        return $credentials;
    }

    public function requestServiceValidate(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface {
        $serviceValidateService = new ServiceValidate(
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

        if (null === $response = $serviceValidateService->handle($request)) {
            $this
                ->getLogger()
                ->error('Error during the service validate request.');

            return null;
        }

        $credentials = $serviceValidateService->getCredentials($response);

        if (null === $credentials) {
            $this
                ->getLogger()
                ->error('Unable to authenticate the user.');
        }

        return $credentials;
    }

    public function requestTicketValidation(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface {
        if (false === $this->supportAuthentication($request, $parameters)) {
            return null;
        }

        /** @var string $ticket */
        $ticket = Uri::getParam(
            $request->getUri(),
            'ticket',
            ''
        );

        $parameters += ['ticket' => $ticket];

        return true === $this->proxyMode()
            ? $this->requestProxyValidate($request, $parameters)
            : $this->requestServiceValidate($request, $parameters);
    }

    public function supportAuthentication(
        RequestInterface $request,
        array $parameters = []
    ): bool {
        return array_key_exists('ticket', $parameters) || Uri::hasParams($request->getUri(), 'ticket');
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
