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
use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
    public function authenticate(
        RequestInterface $request,
        array $parameters = []
    ): ?array;

    public function detect(ResponseInterface $response): IntrospectionInterface;

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
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function handleProxyCallback(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

    /**
     * If not authenticated, redirect to CAS login.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function login(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

    /**
     * Redirect to CAS logout.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function logout(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

    /**
     * Request a proxy ticket.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function requestProxyTicket(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

    /**
     * Request a proxy validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function requestProxyValidate(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

    /**
     * Request a service validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function requestServiceValidate(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

    /**
     * Request a ticket validation.
     *
     * @param array[]|string[] $parameters
     *   The parameters related to the service.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     *   An HTTP response or null.
     */
    public function requestTicketValidation(
        RequestInterface $request,
        array $parameters = []
    ): ?ResponseInterface;

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
        RequestInterface $request,
        array $parameters = []
    ): bool;
}
