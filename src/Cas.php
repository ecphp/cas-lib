<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Handler\ProxyCallback;
use EcPhp\CasLib\Middleware\Login;
use EcPhp\CasLib\Middleware\Logout;
use EcPhp\CasLib\Response\Contract\Proxy as ContractProxy;
use EcPhp\CasLib\Response\Contract\ProxyServiceValidate;
use EcPhp\CasLib\Response\Proxy as ResponseProxy;
use EcPhp\CasLib\Response\ServiceValidate as ResponseServiceValidate;
use EcPhp\CasLib\Service\Proxy;
use EcPhp\CasLib\Service\ResponseNormalizerInterface;
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

    private ResponseNormalizerInterface $responseNormalizer;

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
        ResponseNormalizerInterface $responseNormalizer
    ) {
        $this->properties = $properties;
        $this->client = $client;
        $this->uriFactory = $uriFactory;
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->responseNormalizer = $responseNormalizer;
    }

    public function authenticate(ServerRequestInterface $serverRequest, array $parameters = []): array
    {
        return $this->requestTicketValidation($serverRequest, $parameters)->getCredentials();
    }

    public function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    public function handleLogin(ServerRequestInterface $serverRequest, array $parameters = []): ResponseInterface
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

        return $login->handle($serverRequest);
    }

    public function handleLogout(ServerRequestInterface $serverRequest, array $parameters = []): ResponseInterface
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

        return $logout->handle($serverRequest);
    }

    public function handleProxyCallback(ServerRequestInterface $serverRequest, array $parameters = []): ResponseInterface
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

        return $proxyCallback->handle($serverRequest);
    }

    public function requestProxyTicket(ServerRequestInterface $serverRequest, array $parameters = []): ContractProxy
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

        $properties = $this->getProperties()['protocol']['proxy'] ?? [];
        $parameters = $parameters + ($properties['default_parameters'] ?? []);
        $format = $parameters['format'] ?? 'XML';

        $response = new ResponseProxy(
            $proxyRequestService->handle($serverRequest),
            $format,
            $this->getResponseNormalizer(),
            $this->getCache(),
            $this->getStreamFactory(),
            $this->getLogger()
        );

        if (true === $response->isFailure()) {
            // TODO: Proper exception message.
            throw new Exception('Failure');
        }

        return $response;
    }

    public function requestTicketValidation(ServerRequestInterface $serverRequest, array $parameters = []): ResponseServiceValidate
    {
        if (false === $this->supportAuthentication($serverRequest, $parameters)) {
            throw new Exception('The request does not support authentication.');
        }

        $protocolProperties = $this->getProperties()['protocol']['serviceValidate'] ?? [];
        $parameters = $parameters + $protocolProperties['default_parameters'] ?? [];
        $format = $parameters['format'] ?? 'XML';

        $parameters += [
            'service' => (string) $serverRequest->getUri(),
            'ticket' => Uri::getParam($serverRequest->getUri(), 'ticket'),
        ];

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

        $response = new ResponseServiceValidate(
            $serviceValidateService->handle($serverRequest),
            $format,
            $this->getResponseNormalizer(),
            $this->getCache(),
            $this->getStreamFactory(),
            $this->getLogger()
        );

        if (true === $response->isFailure()) {
            // TODO: Proper exception message.
            throw new Exception('Failure');
        }

        return $response->withPgtIou();
    }

    public function supportAuthentication(ServerRequestInterface $serverRequest, array $parameters = []): bool
    {
        return array_key_exists('ticket', $parameters) || Uri::hasParams($serverRequest->getUri(), 'ticket');
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

    private function getResponseNormalizer(): ResponseNormalizerInterface
    {
        return $this->responseNormalizer;
    }

    private function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    private function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    private function requestServiceValidate(ServerRequestInterface $serverRequest, array $parameters = []): ProxyServiceValidate
    {
    }
}
