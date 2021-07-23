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
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

final class Cas implements CasInterface
{
    private CacheItemPoolInterface $cache;

    private ClientInterface $client;

    private IntrospectorInterface $introspector;

    private LoggerInterface $logger;

    private PropertiesInterface $properties;

    private Psr17Interface $psr17;

    private ServerRequestInterface $serverRequest;

    public function __construct(
        ServerRequestInterface $serverRequest,
        PropertiesInterface $properties,
        ClientInterface $client,
        Psr17Interface $psr17,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        IntrospectorInterface $introspector
    ) {
        $this->serverRequest = $serverRequest;
        $this->properties = $properties;
        $this->client = $client;
        $this->psr17 = $psr17;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->introspector = $introspector;
    }

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

    public function getProperties(): PropertiesInterface
    {
        return $this->properties;
    }

    public function handleProxyCallback(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $proxyCallback = new ProxyCallback(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getPsr17(),
            $this->getCache(),
            $this->getLogger()
        );

        return $response ?? $proxyCallback->handle();
    }

    public function login(array $parameters = []): ?ResponseInterface
    {
        $login = new Login(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getPsr17(),
            $this->getCache(),
            $this->getLogger()
        );

        return $login->handle();
    }

    public function logout(array $parameters = []): ?ResponseInterface
    {
        $logout = new Logout(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getPsr17(),
            $this->getCache(),
            $this->getLogger()
        );

        return $logout->handle();
    }

    public function requestProxyTicket(array $parameters = [], ?ResponseInterface $response = null): ?ResponseInterface
    {
        $proxyRequestService = new Proxy(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getPsr17(),
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

    public function requestProxyValidate(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $proxyValidateService = new ProxyValidate(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getPsr17(),
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

    public function requestServiceValidate(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $serviceValidateService = new ServiceValidate(
            $this->getServerRequest(),
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getPsr17(),
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

    private function getIntrospector(): IntrospectorInterface
    {
        return $this->introspector;
    }

    private function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function getPsr17(): Psr17Interface
    {
        return $this->psr17;
    }

    private function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    private function proxyMode(): bool
    {
        return isset($this->getProperties()['protocol']['serviceValidate']['default_parameters']['pgtUrl']);
    }
}
