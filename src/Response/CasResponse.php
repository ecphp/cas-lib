<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use DOMDocument;
use DOMXPath;
use EcPhp\CasLib\Response\Contract\CasResponseInterface;
use EcPhp\CasLib\Service\ResponseNormalizerInterface;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

abstract class CasResponse implements CasResponseInterface
{
    protected CacheItemPoolInterface $cache;

    protected string $format;

    protected LoggerInterface $logger;

    protected ResponseInterface $response;

    protected StreamFactoryInterface $streamFactory;

    private ResponseNormalizerInterface $responseNormalizer;

    public function __construct(ResponseInterface $response, string $format, ResponseNormalizerInterface $responseNormalizer, CacheItemPoolInterface $cache, StreamFactoryInterface $streamFactory, LoggerInterface $logger)
    {
        $this->response = $response;
        $this->format = $format;
        $this->cache = $cache;
        $this->streamFactory = $streamFactory;
        $this->logger = $logger;
        $this->responseNormalizer = $responseNormalizer;
    }

    public function getBody()
    {
        return $this->response->getBody();
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
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

    public function getInnerResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    public function normalize(): self
    {
        return $this->responseNormalizer->normalize($this);
    }

    public function toArray(): array
    {
        return json_decode((string) $this->normalize()->getBody(), true);
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

    public function withFormat(string $format): CasResponseInterface
    {
        $clone = clone $this;
        $clone->format = $format;

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

    public function withPgtIou(): self
    {
        $parsedResponse = $this->toArray();

        if (false === array_key_exists('serviceResponse', $parsedResponse)) {
            return $this;
        }

        if (false === array_key_exists('authenticationSuccess', $parsedResponse['serviceResponse'])) {
            return $this;
        }

        $proxyGrantingTicket = array_key_exists(
            'proxyGrantingTicket',
            $parsedResponse['serviceResponse']['authenticationSuccess']
        );

        if (false === $proxyGrantingTicket) {
            throw new Exception('No PGT found in the response.');
        }

        $pgt = $parsedResponse['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

        if (false === $this->getCache()->hasItem($pgt)) {
            throw new Exception('CAS validation failed: pgtIou not found in the cache.');
        }

        return $this
            ->withBody(
                $this
                    ->getStreamFactory()
                    ->createStream(
                        str_replace(
                            $pgt,
                            $this->getCache()->getItem($pgt)->get(),
                            (string) $this->getBody()
                        )
                    )
            );
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

    private function removeDomNamespace(DOMDocument $doc, string $namespace): void
    {
        $query = sprintf('//*[namespace::%s and not(../namespace::%s)]', $namespace, $namespace);

        foreach ((new DOMXPath($doc))->query($query) as $node) {
            $node->removeAttributeNS($node->lookupNamespaceURI($namespace), $namespace);
        }
    }
}
