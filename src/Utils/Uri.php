<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Utils;

use Generator;
use Psr\Http\Message\UriInterface;

/**
 * Class Uri.
 */
final class Uri
{
    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param string $param
     * @param string|null $default
     *
     * @return string|null
     */
    public static function getParam(UriInterface $uri, string $param, ?string $default = null): ?string
    {
        return self::getParams($uri)[$param] ?? $default;
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     *
     * @return string[]
     */
    public static function getParams(UriInterface $uri): array
    {
        return iterator_to_array(self::parseStr($uri->getQuery()));
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param string ...$keys
     *
     * @return bool
     */
    public static function hasParams(UriInterface $uri, string ...$keys): bool
    {
        return array_diff_key(array_flip($keys), self::getParams($uri)) === [];
    }

    /**
     * Remove one or more parameters from an URI.
     *
     * @param \Psr\Http\Message\UriInterface $uri
     *   The URI.
     * @param string ...$keys
     *   The parameter(s) to remove.
     *
     * @return \Psr\Http\Message\UriInterface
     *   A new URI without the parameter(s) to remove.
     */
    public static function removeParams(UriInterface $uri, string ...$keys): UriInterface
    {
        return $uri
            ->withQuery(
                http_build_query(
                    array_diff_key(
                        self::getParams($uri),
                        array_flip($keys)
                    )
                )
            );
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param string $key
     * @param string $value
     * @param bool $force
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public static function withParam(
        UriInterface $uri,
        string $key,
        string $value,
        bool $force = true
    ): UriInterface {
        $params = self::getParams($uri) + [$key => $value];

        if (true === $force) {
            $params[$key] = $value;
        }

        return $uri->withQuery(http_build_query($params));
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param string[] $params
     * @param bool $force
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public static function withParams(
        UriInterface $uri,
        array $params,
        bool $force = true
    ): UriInterface {
        foreach ($params as $key => $value) {
            $uri = self::withParam($uri, $key, $value, $force);
        }

        return $uri;
    }

    /**
     * Custom parse_str() function that doesn't alter the parameters key value.
     *
     * @see: https://github.com/ecphp/cas-lib/issues/5.
     *
     * @param string $queryString
     *
     * @return Generator<string, string>
     */
    private static function parseStr(string $queryString): Generator
    {
        $encodedQueryString = preg_replace_callback(
            '/(^|(?<=&))[^=[&]+/',
            static function (array $key): string {
                return bin2hex(urldecode(current($key)));
            },
            $queryString
        );

        if (null === $encodedQueryString) {
            return yield from [];
        }

        parse_str(
            $encodedQueryString,
            $parameters
        );

        foreach ($parameters as $key => $value) {
            yield (string) hex2bin((string) $key) => $value;
        }
    }
}
