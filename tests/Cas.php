<?php

declare(strict_types=1);

namespace tests\EcPhp\CasLib;

use EcPhp\CasLib\CasInterface;
use EcPhp\CasLib\Configuration\PropertiesInterface;
use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cas implements CasInterface
{
    /**
     * @var \EcPhp\CasLib\Cas
     */
    private $cas;

    public function __construct(
        CasInterface $cas
    ) {
        $this->cas = $cas;
    }

    public function authenticate(array $parameters = []): ?array
    {
        return $this->cas->authenticate($parameters);
    }

    public function detect(
        ResponseInterface $response
    ): IntrospectionInterface {
        return $this->cas->detect($response);
    }

    public function getProperties(): PropertiesInterface
    {
        return $this->cas->getProperties();
    }

    public function handleProxyCallback(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        return $this->cas->handleProxyCallback($parameters, $response);
    }

    public function login(array $parameters = []): ?ResponseInterface
    {
        return $this->cas->login($parameters);
    }

    public function logout(array $parameters = []): ?ResponseInterface
    {
        return $this->cas->logout($parameters);
    }

    public function requestProxyTicket(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        return $this->cas->requestProxyTicket($parameters, $response);
    }

    public function requestProxyValidate(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        return $this->cas->requestProxyValidate($parameters, $response);
    }

    public function requestServiceValidate(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        return $this->cas->requestServiceValidate($parameters, $response);
    }

    public function requestTicketValidation(
        array $parameters = [],
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        return $this->cas->requestTicketValidation($parameters, $response);
    }

    public function supportAuthentication(array $parameters = []): bool
    {
        return $this->cas->supportAuthentication($parameters);
    }

    public function withServerRequest(
        ServerRequestInterface $serverRequest
    ): CasInterface {
        $clone = clone $this;
        $clone->cas = $clone->cas->withServerRequest($serverRequest);

        return $clone;
    }
}
