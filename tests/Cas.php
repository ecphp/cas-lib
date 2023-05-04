<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\CasLib;

use EcPhp\CasLib\Contract\CasInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Cas implements CasInterface
{
    public function __construct(
        private readonly CasInterface $cas
    ) {
    }

    public function authenticate(ServerRequestInterface $request, array $parameters = []): array
    {
        return $this->cas->authenticate($request, $parameters);
    }

    public function handleProxyCallback(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->handleProxyCallback($request, $parameters);
    }

    public function login(ServerRequestInterface $request, array $parameters = []): ResponseInterface
    {
        return $this->cas->login($request, $parameters);
    }

    public function logout(ServerRequestInterface $request, array $parameters = []): ResponseInterface
    {
        return $this->cas->logout($request, $parameters);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->cas->process($request, $handler);
    }

    public function requestProxyTicket(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestProxyTicket($request, $parameters);
    }

    public function requestServiceValidate(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestServiceValidate($request, $parameters);
    }

    public function requestTicketValidation(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestTicketValidation($request, $parameters);
    }

    public function supportAuthentication(ServerRequestInterface $request, array $parameters = []): bool
    {
        return $this->cas->supportAuthentication($request, $parameters);
    }
}
