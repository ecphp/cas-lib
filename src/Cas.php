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
use Exception;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;

// phpcs:disable Generic.Files.LineLength.TooLong

final class Cas implements CasInterface
{
    private CacheItemPoolInterface $cache;

    private ClientInterface $client;

    private IntrospectorInterface $introspector;

    private PropertiesInterface $properties;

    private Psr17Interface $psr17;

    public function __construct(
        PropertiesInterface $properties,
        ClientInterface $client,
        Psr17Interface $psr17,
        CacheItemPoolInterface $cache,
        IntrospectorInterface $introspector
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->introspector = $introspector;
        $this->properties = $properties;
        $this->psr17 = $psr17;
    }

    public function authenticate(RequestInterface $request, array $parameters = []): array
    {
        $response = $this->requestTicketValidation($request, $parameters);

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
        array $parameters = []
    ): ResponseInterface {
        $proxyCallback = new ProxyCallback(
            $parameters,
            $this->getProperties(),
            $this->getPsr17(),
            $this->getCache()
        );

        return $proxyCallback->handle($request);
    }

    public function login(RequestInterface $request, array $parameters = []): ResponseInterface
    {
        $login = new Login(
            $parameters,
            $this->getProperties(),
            $this->getPsr17(),
            $this->getCache()
        );

        return $login->handle($request);
    }

    public function logout(RequestInterface $request, array $parameters = []): ResponseInterface
    {
        $logout = new Logout(
            $parameters,
            $this->getProperties(),
            $this->getPsr17(),
            $this->getCache()
        );

        return $logout->handle($request);
    }

    public function requestProxyTicket(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        $proxyRequestService = new Proxy(
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getPsr17(),
            $this->getCache(),
            $this->getIntrospector()
        );

        $response = $proxyRequestService->handle($request);

        return $proxyRequestService->getCredentials($response);
    }

    public function requestProxyValidate(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        $proxyValidateService = new ProxyValidate(
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getPsr17(),
            $this->getCache(),
            $this->getIntrospector()
        );

        $response = $proxyValidateService->handle($request);

        return $proxyValidateService->getCredentials($response);
    }

    public function requestServiceValidate(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        $serviceValidateService = new ServiceValidate(
            $parameters,
            $this->getProperties(),
            $this->getHttpClient(),
            $this->getPsr17(),
            $this->getCache(),
            $this->getIntrospector()
        );

        $response = $serviceValidateService->handle($request);

        return $serviceValidateService->getCredentials($response);
    }

    public function requestTicketValidation(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        if (false === $this->supportAuthentication($request, $parameters)) {
            throw new Exception('This request does not support ticket validation.');
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

    private function getPsr17(): Psr17Interface
    {
        return $this->psr17;
    }

    private function proxyMode(): bool
    {
        return isset($this->getProperties()['protocol']['serviceValidate']['default_parameters']['pgtUrl']);
    }
}
