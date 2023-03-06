<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Type;

use EcPhp\CasLib\Contract\Response\CasResponseInterface;
use EcPhp\CasLib\Utils\Response as ResponseUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class CasResponse implements CasResponseInterface
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function toArray(): array
    {
        return (new ResponseUtils())->toArray($this->response);
    }

    public function withAddedHeader($name, $value): CasResponseInterface
    {
        $clone = clone $this;
        $clone->response = $this->response->withAddedHeader($name, $value);

        return $clone;
    }

    public function withBody(StreamInterface $body): CasResponseInterface
    {
        $clone = clone $this;
        $clone->response = $this->response->withBody($body);

        return $clone;
    }

    public function withHeader($name, $value): CasResponseInterface
    {
        $clone = clone $this;
        $clone->response = $this->response->withHeader($name, $value);

        return $clone;
    }

    public function withoutHeader($name): CasResponseInterface
    {
        $clone = clone $this;
        $clone->response = $this->response->withoutHeader($name);

        return $clone;
    }

    public function withProtocolVersion($version): CasResponseInterface
    {
        $clone = clone $this;
        $clone->response = $this->response->withProtocolVersion($version);

        return $clone;
    }

    public function withStatus($code, $reasonPhrase = ''): CasResponseInterface
    {
        $clone = clone $this;
        $clone->response = $this->response->withStatus($code, $reasonPhrase);

        return $clone;
    }
}
