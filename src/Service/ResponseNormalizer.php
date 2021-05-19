<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use DOMDocument;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use LSS\XML2Array;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use const JSON_ERROR_NONE;
use const LIBXML_NOBLANKS;
use const LIBXML_NOCDATA;
use const LIBXML_NONET;
use const LIBXML_NSCLEAN;

final class ResponseNormalizer implements ResponseNormalizerInterface
{
    private StreamFactoryInterface $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    public function normalize(ResponseInterface $response): ResponseInterface
    {
        if (200 !== $response->getStatusCode()) {
            throw new InvalidArgumentException('Unable to detect the response format.');
        }

        $fromFormat = null;

        if (true === $response->hasHeader('Content-Type')) {
            $header = substr($response->getHeaderLine('Content-Type'), 0, 16);

            switch ($header) {
                case 'application/json':
                    $fromFormat = 'JSON';

                    break;

                case 'application/xml':
                    $fromFormat = 'XML';

                    break;
            }
        }

        if (null === $fromFormat) {
            throw new Exception('Unable to detect the response format.');
        }

        $parsed = $this->parse($response, $fromFormat);

        return $response
            ->withBody(
                $this
                    ->streamFactory
                    ->createStream(
                        json_encode(
                            $parsed
                        )
                    )
            );
    }

    private function parse(ResponseInterface $response, string $format): array
    {
        $body = (string) $response->getBody();

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

    private function removeDomNamespace(DOMDocument $doc, string $namespace): void
    {
        $query = sprintf('//*[namespace::%s and not(../namespace::%s)]', $namespace, $namespace);

        foreach ((new DOMXPath($doc))->query($query) as $node) {
            $node->removeAttributeNS($node->lookupNamespaceURI($namespace), $namespace);
        }
    }
}
