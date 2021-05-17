<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Handler\ProxyCallback;
use EcPhp\CasLib\Redirect\Login;
use EcPhp\CasLib\Redirect\Logout;
use EcPhp\CasLib\Response\Contract\CasResponseInterface;
use EcPhp\CasLib\Response\Contract\ProxyServiceValidate;
use EcPhp\CasLib\Response\ServiceValidate as ResponseServiceValidate;
use EcPhp\CasLib\Service\Proxy;
use EcPhp\CasLib\Service\ProxyValidate;
use EcPhp\CasLib\Service\ServiceValidate;
use EcPhp\CasLib\Utils\Uri;
use Exception;
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
    private CacheItemPoolInterface $cache;

    private ClientInterface $client;

    private LoggerInterface $logger;

    private PropertiesInterface $properties;

    private RequestFactoryInterface $requestFactory;

    private ResponseFactoryInterface $responseFactory;

    private ServerRequestInterface $serverRequest;

    private StreamFactoryInterface $streamFactory;

    private UriFactoryInterface $uriFactory;

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

    public function authenticate(array $parameters = []): array
    {
        return $this->requestTicketValidation($parameters)->getCredentials();
    }

    public function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    public function handleProxyCallback(array $parameters = []): ResponseInterface
    {
        $proxyCallback = new ProxyCallback(
            $parameters,
            $this->getProperties(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $proxyCallback->handle($this->getServerRequest());
    }

    public function login(array $parameters = []): ResponseInterface
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

        return $login->handle($this->getServerRequest());
    }

    public function logout(array $parameters = []): ResponseInterface
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

        return $logout->handle($this->getServerRequest());
    }

    public function requestProxyTicket(array $parameters = []): ResponseInterface
    {
        $proxyRequestService = new Proxy(
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getRequestFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $proxyRequestService->handle($this->getServerRequest())->getCredentials();
    }

    public function requestProxyValidate(array $parameters = []): ProxyServiceValidate
    {
        $proxyValidateService = new ProxyValidate(
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getRequestFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $proxyValidateService->handle($this->getServerRequest());
    }

    public function requestServiceValidate(array $parameters = []): ResponseServiceValidate
    {
        $serviceValidateService = new ServiceValidate(
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getUriFactory(),
            $this->getResponseFactory(),
            $this->getRequestFactory(),
            $this->getStreamFactory(),
            $this->getCache(),
            $this->getLogger()
        );

        return $serviceValidateService->handle($this->getServerRequest());
    }

    public function requestTicketValidation(array $parameters = []): CasResponseInterface
    {
        if (false === $this->supportAuthentication($parameters)) {
            throw new Exception('The request does not support authentication.');
        }

        /** @var string $ticket */
        $ticket = Uri::getParam(
            $this->getServerRequest()->getUri(),
            'ticket',
            ''
        );

        $parameters += ['ticket' => $ticket];

        return true === $this->proxyMode() ?
            $this->requestProxyValidate($parameters) :
            $this->requestServiceValidate($parameters);
    }

    public function supportAuthentication(array $parameters = []): bool
    {
        return array_key_exists('ticket', $parameters) || Uri::hasParams($this->getServerRequest()->getUri(), 'ticket');
    }

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
