<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\CasLib;

use EcPhp\CasLib\CasInterface;
use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Cas implements CasInterface
{
    private CasInterface $cas;

    public function __construct(
        CasInterface $cas
    ) {
        $this->cas = $cas;
    }

    public function authenticate(RequestInterface $request, array $parameters = []): array
    {
        return $this->cas->authenticate($request, $parameters);
    }

    public function detect(
        ResponseInterface $response
    ): IntrospectionInterface {
        return $this->cas->detect($response);
    }

    public function handleProxyCallback(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->handleProxyCallback($request, $parameters);
    }

    public function login(RequestInterface $request, array $parameters = []): ResponseInterface
    {
        return $this->cas->login($request, $parameters);
    }

    public function logout(RequestInterface $request, array $parameters = []): ResponseInterface
    {
        return $this->cas->logout($request, $parameters);
    }

    public function requestProxyTicket(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestProxyTicket($request, $parameters);
    }

    public function requestProxyValidate(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestProxyValidate($request, $parameters);
    }

    public function requestServiceValidate(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestServiceValidate($request, $parameters);
    }

    public function requestTicketValidation(
        RequestInterface $request,
        array $parameters = []
    ): ResponseInterface {
        return $this->cas->requestTicketValidation($request, $parameters);
    }

    public function supportAuthentication(RequestInterface $request, array $parameters = []): bool
    {
        return $this->cas->supportAuthentication($request, $parameters);
    }
}
