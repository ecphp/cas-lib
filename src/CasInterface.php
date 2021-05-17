<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib;

use EcPhp\CasLib\Configuration\PropertiesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CasInterface
{
    /**
     * Authenticate the request.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return array[]|null
     *   The user response if authenticated, null otherwise.
     */
    public function authenticate(array $parameters = []): ?array;

    /**
     * Get the CAS properties.
     *
     * @return \EcPhp\CasLib\Configuration\PropertiesInterface
     *   The properties.
     */
    public function getProperties(): PropertiesInterface;

    /**
     * Handle the request made on the proxy callback URL.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function handleProxyCallback(array $parameters = []): ResponseInterface;

    /**
     * If not authenticated, redirect to CAS login.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function login(array $parameters = []): ResponseInterface;

    /**
     * Redirect to CAS logout.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function logout(array $parameters = []): ResponseInterface;

    /**
     * Request a proxy ticket.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function requestProxyTicket(array $parameters = []): ResponseInterface;

    /**
     * Request a proxy validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function requestProxyValidate(array $parameters = []): ResponseInterface;

    /**
     * Request a service validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function requestServiceValidate(array $parameters = []): ResponseInterface;

    /**
     * Request a ticket validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   An HTTP response or null.
     */
    public function requestTicketValidation(array $parameters = []): ResponseInterface;

    /**
     * Check if the request needs to be authenticated.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return bool
     *   True if it can run the authentication, false otherwise.
     */
    public function supportAuthentication(array $parameters = []): bool;

    /**
     * Update the server request in use.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     *   The server request.
     *
     * @return \EcPhp\CasLib\CasInterface
     *   The cas service.
     */
    public function withServerRequest(ServerRequestInterface $serverRequest): CasInterface;
}
