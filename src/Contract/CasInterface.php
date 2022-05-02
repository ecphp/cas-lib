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
use Psr\Http\Server\RequestHandlerInterface;

interface CasInterface extends MiddlewareInterface
{
    /**
     * Authenticate the request.
     *
     * Perform an authentication. On success, return the credentials.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
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
     * This handle is in use when proxy mode is enabled through the existence
     * of the `pgtUrl` property. This handler will extract the PGTIOU and PGTID
     * from the request and save it in the cache.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @throws CasExceptionInterface
     */
    public function handleProxyCallback(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * If not authenticated, redirect to CAS login.
     *
     * Create the CAS login redirect response so the user can authenticate the
     * user against the CAS server.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @throws CasExceptionInterface
     */
    public function login(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Redirect to CAS logout.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @throws CasExceptionInterface
     */
    public function logout(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * @throws CasExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    /**
     * Request a proxy ticket.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @throws CasExceptionInterface
     */
    public function requestProxyTicket(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Request a service validation.
     *
     * This validation will perform a service validation. If the parameter
     * `pgtUrl` is set in the properties, it will perform a service validation
     * with proxy and will replace the PGT IOU with a PGT ID in the response.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @throws CasExceptionInterface
     */
    public function requestServiceValidate(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Request a ticket validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @throws CasExceptionInterface
     */
    public function requestTicketValidation(
        ServerRequestInterface $request,
        array $parameters = []
    ): ResponseInterface;

    /**
     * Check if the request supports authentication.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service. They will override
     *   those in the request URI.
     *
     * @return bool
     *   True if it can be authenticated, false otherwise.
     */
    public function supportAuthentication(
        ServerRequestInterface $request,
        array $parameters = []
    ): bool;
}
