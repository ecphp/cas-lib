<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Utils;

use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Exception\CasExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use VeeWee\Xml\Dom\Traverser\Visitor\RemoveNamespaces;

use function VeeWee\Xml\Dom\Configurator\traverse;
use function VeeWee\Xml\Encoding\xml_decode;

use const JSON_THROW_ON_ERROR;

final class Response
{
    /**
     * @throws CasExceptionInterface
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

        try {
            $response = match (true) {
                str_starts_with($header, 'application/json') => (array) json_decode($body, true, 512, JSON_THROW_ON_ERROR),
                str_starts_with($header, 'text/html'),
                str_starts_with($header, 'text/xml'),
                str_starts_with($header, 'application/xml') => xml_decode($body, traverse(new RemoveNamespaces())),
                default => throw CasException::unsupportedResponseFormat($header)
            };
        } catch (Throwable $exception) {
            throw CasException::unableToConvertResponse($header, $exception);
        }

        return $response;
    }
}
