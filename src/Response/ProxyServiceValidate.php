<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\ProxyServiceValidate as ProxyServiceValidateInterface;
use Psr\Http\Message\StreamInterface;

final class ProxyServiceValidate extends CasResponse implements ProxyServiceValidateInterface
{
    public function getBody()
    {
        return $this->response->getBody();
    }

    public function getCredentials(): array
    {
        return $this->parse()['serviceResponse']['authenticationSuccess'];
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    public function getProxies(): array
    {
        $hasProxy = isset($this->parse()['serviceResponse']['authenticationSuccess']['proxies']);

        return true === $hasProxy ?
            $this->parse()['serviceResponse']['authenticationSuccess']['proxies'] :
            [];
    }

    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        $clone->response = $this->response->withAddedHeader($name, $value);

        return $clone;
    }

    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->response = $this->response->withBody($body);

        return $clone;
    }

    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->response = $this->response->withHeader($name, $value);

        return $clone;
    }

    public function withoutHeader($name)
    {
        $clone = clone $this;
        $clone->response = $this->response->withoutHeader($name);

        return $clone;
    }

    public function withProtocolVersion($version)
    {
        $clone = clone $this;
        $clone->response = $this->response->withProtocolVersion($version);

        return $clone;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $clone = clone $this;
        $clone->response = $this->response->withStatus($code, $reasonPhrase);

        return $clone;
    }
}
