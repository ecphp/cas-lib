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
use EcPhp\CasLib\Exception\CasException;
use ErrorException;
use LSS\XML2Array;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use const JSON_THROW_ON_ERROR;
use const LIBXML_NOBLANKS;
use const LIBXML_NOCDATA;
use const LIBXML_NONET;
use const LIBXML_NSCLEAN;

final class Response
{
    /**
     * @throws CasException
     *
     * @return mixed[]
     */
    public function toArray(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ('' === $body) {
            throw CasException::emptyResponseBodyFailure();
        }

        if (false === $response->hasHeader('Content-Type')) {
            throw CasException::missingResponseContentTypeHeader();
        }

        $header = substr($response->getHeaderLine('Content-Type'), 0, 16);

        switch ($header) {
            case 'application/json':
                try {
                    $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                } catch (Throwable $exception) {
                    throw CasException::unableToConvertResponseFromJson($exception);
                }

                return $json;

            case 'application/xml':
                set_error_handler(
                    static function ($errno, $errstr, $errfile, $errline): void {
                        throw CasException::unableToLoadXml(
                            new ErrorException($errstr, 0, $errno, $errfile, $errline)
                        );
                    }
                );
                $dom = new DOMDocument();
                $dom
                    ->loadXML(
                        $body,
                        LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NONET
                    );
                restore_error_handler();

                $this->removeDomNamespace($dom, 'cas');

                try {
                    $data = XML2Array::createArray($dom);
                } catch (Throwable $exception) {
                    throw CasException::unableToConvertResponseFromXml($exception);
                }

                return $data;
        }

        throw CasException::unsupportedResponseFormat($header);
    }

    private function removeDomNamespace(DOMDocument $doc, string $namespace): void
    {
        $query = sprintf('//*[namespace::%s and not(../namespace::%s)]', $namespace, $namespace);

        foreach ((new DOMXPath($doc))->query($query) as $node) {
            $node->removeAttributeNS($node->lookupNamespaceURI($namespace), $namespace);
        }
    }
}
