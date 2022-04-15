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
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Handler\Proxy;
use EcPhp\CasLib\Handler\ProxyCallback;
use EcPhp\CasLib\Handler\ProxyValidate;
use EcPhp\CasLib\Handler\ServiceValidate;
use EcPhp\CasLib\Redirect\Login;
use EcPhp\CasLib\Redirect\Logout;
use EcPhp\CasLib\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Utils\Uri;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function array_key_exists;

// phpcs:disable Generic.Files.LineLength.TooLong

final class Cas implements CasInterface
{
    private CacheItemPoolInterface $cache;

    private CasResponseBuilderInterface $casResponseBuilder;

    private ClientInterface $client;

    private PropertiesInterface $properties;

    private Psr17Interface $psr17;

    public function __construct(
        PropertiesInterface $properties,
        ClientInterface $client,
        Psr17Interface $psr17,
        CacheItemPoolInterface $cache,
        CasResponseBuilderInterface $casResponseBuilder
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->casResponseBuilder = $casResponseBuilder;
        $this->properties = $properties;
        $this->psr17 = $psr17;
    }

    public function authenticate(
        ServerRequestInterface $request,
        array $parameters = []
    ): array {
        try {
            $response = $this->requestTicketValidation($request, $parameters);
        } catch (Throwable $exception) {
            throw CasException::unableToAuthenticate($exception);
        }

        try {
            $credentials = $this
                ->casResponseBuilder
                ->fromResponse($response)
                ->toArray();
        } catch (Throwable $exception) {
            throw CasException::unableToAuthenticate($exception);
        }

        return $credentials;
    }

    public function handleProxyCallback(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this
            ->process(
                $request,
                new ProxyCallback(
                    $parameters,
                    $this->cache,
                    $this->casResponseBuilder,
                    $this->client,
                    $this->properties,
                    $this->psr17,
                )
            );
    }

    public function login(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this
            ->process(
                $request,
                new Login(
                    $parameters,
                    $this->cache,
                    $this->casResponseBuilder,
                    $this->client,
                    $this->properties,
                    $this->psr17,
                )
            );
    }

    public function logout(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this
            ->process(
                $request,
                new Logout(
                    $parameters,
                    $this->cache,
                    $this->casResponseBuilder,
                    $this->client,
                    $this->properties,
                    $this->psr17,
                )
            );
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $handler->handle($request);
    }

    public function requestProxyTicket(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this
            ->process(
                $request,
                new Proxy(
                    $parameters,
                    $this->cache,
                    $this->casResponseBuilder,
                    $this->client,
                    $this->properties,
                    $this->psr17,
                )
            );
    }

    public function requestProxyValidate(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this
            ->process(
                $request,
                new ProxyValidate(
                    $parameters,
                    $this->cache,
                    $this->casResponseBuilder,
                    $this->client,
                    $this->properties,
                    $this->psr17,
                )
            );
    }

    public function requestServiceValidate(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this
            ->process(
                $request,
                new ServiceValidate(
                    $parameters,
                    $this->cache,
                    $this->casResponseBuilder,
                    $this->client,
                    $this->properties,
                    $this->psr17,
                )
            );
    }

    public function requestTicketValidation(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        if (false === $this->supportAuthentication($request, $parameters)) {
            throw CasException::unsupportedRequest();
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
        ServerRequestInterface $request,
        array $parameters = []
    ): bool {
        return array_key_exists('ticket', $parameters) || Uri::hasParams($request->getUri(), 'ticket');
    }

    private function proxyMode(): bool
    {
        return isset($this->properties['protocol']['serviceValidate']['default_parameters']['pgtUrl']);
    }
}
