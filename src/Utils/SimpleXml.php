<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Utils;

use SimpleXMLElement;

use const LIBXML_NOBLANKS;
use const LIBXML_NOCDATA;

/**
 * Class SimpleXml.
 */
final class SimpleXml
{
    public static function fromString(string $data): ?SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $parsed = simplexml_load_string(
            $data,
            'SimpleXMLElement',
            LIBXML_NOCDATA | LIBXML_NOBLANKS,
            'cas',
            true
        );

        if (false === $parsed) {
            // todo: Log errors from libxml_get_errors().
            return null;
        }

        return $parsed;
    }

    /**
     * @return array[]|null[]|string[]
     */
    public static function toArray(SimpleXMLElement $xml): array
    {
        return [$xml->getName() => self::toArrayRecursive($xml)];
    }

    /**
     * @return array[]
     */
    private static function toArrayRecursive(SimpleXMLElement $element): ?array
    {
        return array_map(
            static function ($node) {
                return $node instanceof SimpleXMLElement ?
                    self::toArrayRecursive($node) :
                    $node;
            },
            (array) $element
        );
    }
}
