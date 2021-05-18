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
use Exception;
use InvalidArgumentException;
use LSS\XML2Array;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

use const JSON_ERROR_NONE;
use const LIBXML_NOBLANKS;
use const LIBXML_NOCDATA;
use const LIBXML_NONET;
use const LIBXML_NSCLEAN;

abstract class CasResponse implements CasResponseInterface
{
    protected CacheItemPoolInterface $cache;

    protected string $format;

    protected LoggerInterface $logger;

    protected ResponseInterface $response;

    protected StreamFactoryInterface $streamFactory;

    public function __construct(ResponseInterface $response, string $format, CacheItemPoolInterface $cache, StreamFactoryInterface $streamFactory, LoggerInterface $logger)
    {
        $this->response = $response;
        $this->format = $format;
        $this->cache = $cache;
        $this->streamFactory = $streamFactory;
        $this->logger = $logger;
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

    public function normalize(): ResponseInterface
    {
        $body = $this->parse();

        if ([] === $body) {
            $this
                ->getLogger()
                ->error(
                    'Unable to parse the response during the normalization process.',
                    [
                        'body' => (string) $this->getBody(),
                    ]
                );

            return $this;
        }

        $body = json_encode($body);

        if (false === $body || JSON_ERROR_NONE !== json_last_error()) {
            $this
                ->getLogger()
                ->error(
                    'Unable to encode the response in JSON during the normalization process.',
                    [
                        'body' => (string) $this->getBody(),
                    ]
                );

            return $this;
        }

        $this
            ->getLogger()
            ->debug('Response normalization succeeded.', ['body' => $body]);

        return $this
            ->withBody($this->getStreamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json')
            ->withFormat('JSON');
    }

    public function parse(): array
    {
        $format = $this->getFormat();
        $body = (string) $this->getBody();

        if ('' === $body) {
            throw new InvalidArgumentException('Empty response body');
        }

        if ('XML' === $format) {
            try {
                $dom = new DOMDocument();

                $dom
                    ->loadXML(
                        $body,
                        LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NONET
                    );

                $this->removeDomNamespace($dom, 'cas');

                $data = XML2Array::createArray($dom);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Unable to parse the response using XML format.', 0, $e);
            }

            return $data;
        }

        if ('JSON' === $format) {
            $json = json_decode($body, true);

            if (null === $json || JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidArgumentException('Unable to parse the response using JSON format.');
            }

            return $json;
        }

        throw new InvalidArgumentException('Unsupported format.');
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

    public function withPgtIou(): CasResponseInterface
    {
        $parsedResponse = $this->parse();

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
