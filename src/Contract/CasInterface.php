<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract;

use EcPhp\CasLib\Exception\CasExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface CasInterface extends MiddlewareInterface
{
    /**
     * Authenticate the request.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @throws CasExceptionInterface
     */
    public function authenticate(
        ServerRequestInterface $request,
        array $parameters = []
    ): array;

    /**
     * Handle the request made on the proxy callback URL.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     */
    public function handleProxyCallback(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * If not authenticated, redirect to CAS login.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     */
    public function login(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Redirect to CAS logout.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     */
    public function logout(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Request a proxy ticket.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     */
    public function requestProxyTicket(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Request a service validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     */
    public function requestServiceValidate(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Request a ticket validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @throws CasExceptionInterface
     */
    public function requestTicketValidation(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Check if the request needs to be authenticated.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return bool
     *   True if it can run the authentication, false otherwise.
     */
    public function supportAuthentication(
        ServerRequestInterface $request,
        array $parameters = []
    ): bool;
}
