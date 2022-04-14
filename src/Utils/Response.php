<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Utils;

use DOMDocument;
use DOMXPath;
use Exception;
use LSS\XML2Array;
use Psr\Http\Message\ResponseInterface;

use const JSON_ERROR_NONE;
use const LIBXML_NOBLANKS;
use const LIBXML_NOCDATA;
use const LIBXML_NONET;
use const LIBXML_NSCLEAN;

final class Response
{
    /**
     * @throws Exception
     *
     * @return mixed[]
     */
    public function toArray(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ('' === $body) {
            throw new Exception('Empty response body');
        }

        if (false === $response->hasHeader('Content-Type')) {
            throw new Exception('Unable to detect response format.');
        }

        $header = substr($response->getHeaderLine('Content-Type'), 0, 16);

        switch ($header) {
            case 'application/json':
                $json = json_decode($body, true);

                if (null === $json || JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception('Unable to parse the response using JSON format.');
                }

                return $json;

            case 'application/xml':
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
                    throw new Exception('Unable to parse the response using XML format.', 0, $e);
                }

                return $data;
        }

        throw new Exception('Unsupported format.');
    }

    private function removeDomNamespace(DOMDocument $doc, string $namespace): void
    {
        $query = sprintf('//*[namespace::%s and not(../namespace::%s)]', $namespace, $namespace);

        foreach ((new DOMXPath($doc))->query($query) as $node) {
            $node->removeAttributeNS($node->lookupNamespaceURI($namespace), $namespace);
        }
    }
}
