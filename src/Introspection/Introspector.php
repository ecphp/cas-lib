<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use DOMDocument;
use DOMXPath;
use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use Exception;
use InvalidArgumentException;
use LSS\XML2Array;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;
use const JSON_ERROR_NONE;
use const LIBXML_NOBLANKS;
use const LIBXML_NOCDATA;
use const LIBXML_NONET;
use const LIBXML_NSCLEAN;

final class Introspector implements IntrospectorInterface
{
    public function detect(ResponseInterface $response): IntrospectionInterface
    {
        $format = null;

        if (200 !== $response->getStatusCode()) {
            throw new Exception('Invalid status code.');
        }

        if (true === $response->hasHeader('Content-Type')) {
            $header = substr($response->getHeaderLine('Content-Type'), 0, 16);

            switch ($header) {
                case 'application/json':
                    $format = 'JSON';

                    break;

                case 'application/xml':
                    $format = 'XML';

                    break;
            }
        }

        if (null === $format) {
            throw new Exception('Unable to detect the response format.');
        }

        $data = $this->parse($response, $format);

        if (false === array_key_exists('serviceResponse', $data)) {
            throw new Exception('Unable to find the response type.');
        }

        if (array_key_exists('authenticationFailure', $data['serviceResponse'])) {
            return new AuthenticationFailure($data, $format, $response);
        }

        if (array_key_exists('proxyFailure', $data['serviceResponse'])) {
            return new ProxyFailure($data, $format, $response);
        }

        if (array_key_exists('authenticationSuccess', $data['serviceResponse']) && array_key_exists('user', $data['serviceResponse']['authenticationSuccess'])) {
            return new ServiceValidate($data, $format, $response);
        }

        if (array_key_exists('proxySuccess', $data['serviceResponse']) && array_key_exists('proxyTicket', $data['serviceResponse']['proxySuccess'])) {
            return new Proxy($data, $format, $response);
        }

        throw new InvalidArgumentException('Unable to find the response type.');
    }

    /**
     * @throws Exception
     *
     * @return mixed[]
     */
    public function parse(ResponseInterface $response, string $format = 'XML'): array
    {
        $body = (string) $response->getBody();

        if ('' === $body) {
            throw new Exception('Empty response body');
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
                throw new Exception('Unable to parse the response using XML format.', 0, $e);
            }

            return $data;
        }

        if ('JSON' === $format) {
            $json = json_decode($body, true);

            if (null === $json || JSON_ERROR_NONE !== json_last_error()) {
                throw new Exception('Unable to parse the response using JSON format.');
            }

            return $json;
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
