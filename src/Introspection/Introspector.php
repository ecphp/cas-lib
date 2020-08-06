<?php

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
            throw new InvalidArgumentException('Unable to detect the response format.');
        }

        if (true === $response->hasHeader('Content-Type')) {
            $header = mb_substr($response->getHeaderLine('Content-Type'), 0, 16);

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
            throw new InvalidArgumentException('Unable to detect the response format.');
        }

        try {
            $data = $this->parse($response, $format);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        if (isset($data['serviceResponse']['authenticationFailure'])) {
            return new AuthenticationFailure($data, $format, $response);
        }

        if (isset($data['serviceResponse']['proxyFailure'])) {
            return new ProxyFailure($data, $format, $response);
        }

        if (isset($data['serviceResponse']['authenticationSuccess']['user'])) {
            return new ServiceValidate($data, $format, $response);
        }

        if (isset($data['serviceResponse']['proxySuccess']['proxyTicket'])) {
            return new Proxy($data, $format, $response);
        }

        throw new InvalidArgumentException('Unable to find the response type.');
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return mixed[]
     */
    public function parse(ResponseInterface $response, string $format = 'XML'): array
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
