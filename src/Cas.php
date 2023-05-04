<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use EcPhp\CasLib\Contract\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Handler\Login;
use EcPhp\CasLib\Handler\Logout;
use EcPhp\CasLib\Handler\Proxy;
use EcPhp\CasLib\Handler\ProxyCallback;
use EcPhp\CasLib\Handler\ServiceValidate;
use EcPhp\CasLib\Middleware\StringBodyResponse;
use EcPhp\CasLib\Utils\Uri;
use Http\Client\Common\PluginClient;
use loophp\psr17\Psr17Interface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function array_key_exists;

final class Cas implements CasInterface
{
    private readonly ClientInterface $client;

    public function __construct(
        private readonly PropertiesInterface $properties,
        ClientInterface $client,
        private readonly Psr17Interface $psr17,
        private readonly CacheItemPoolInterface $cache,
        private readonly CasResponseBuilderInterface $casResponseBuilder
    ) {
        // We do this to make sure that the response body can be retrieved
        // multiple times.
        $this->client = new PluginClient(
            $client,
            [new StringBodyResponse($psr17)]
        );
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
            $casResponse = $this
                ->casResponseBuilder
                ->fromResponse($response);
        } catch (Throwable $exception) {
            throw CasException::unableToAuthenticate($exception);
        }

        try {
            $credentials = $casResponse->toArray();
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

        return $this
            ->requestServiceValidate(
                $request,
                $parameters + ['ticket' => $ticket]
            );
    }

    public function supportAuthentication(
        ServerRequestInterface $request,
        array $parameters = []
    ): bool {
        return array_key_exists('ticket', $parameters) || Uri::hasParams($request->getUri(), 'ticket');
    }
}
