<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Utils;

use InvalidArgumentException;
use League\Uri\Components\Query;
use Psr\Http\Message\UriInterface;
use Throwable;
use TypeError;

final class Uri
{
    /**
     * Get a parameter from the URI query string.
     *
     * @param UriInterface $uri
     *   The URI.
     * @param string $param
     *   The parameter key.
     * @param string $default
     *   The default value if the parameter doesn't exist.
     *
     * @return string
     *   The value of the parameter to get.
     */
    public static function getParam(
        UriInterface $uri,
        string $param,
        string $default = ''
    ): string {
        return self::getParams($uri)[$param] ?? $default;
    }

    /**
     * Get the parameters from the URI query string.
     *
     * @param UriInterface $uri
     *   The URI.
     *
     * @return array<string, string|null>
     *   The parameters, as "key/value" pairs.
     */
    public static function getParams(UriInterface $uri): array
    {
        $pairs = [];

        try {
            $pairs = Query::createFromUri($uri)->pairs();
        } catch (Throwable $exception) {
            // Ignore the exception.
        }

        $associatedPairs = [];

        foreach ($pairs as $key => $value) {
            $associatedPairs[$key] = $value;
        }

        return $associatedPairs;
    }

    /**
     * Check wether an URI has the requested parameters.
     *
     * @param UriInterface $uri
     *   The URI.
     * @param string ...$keys
     *   The parameter keys to check.
     */
    public static function hasParams(UriInterface $uri, string ...$keys): bool
    {
        return [] === array_diff_key(array_flip($keys), self::getParams($uri));
    }

    /**
     * Remove one or more parameters from an URI.
     *
     * @param UriInterface $uri
     *   The URI.
     * @param string ...$keys
     *   The parameter(s) to remove.
     *
     * @return UriInterface
     *   A new URI without the parameter(s) to remove.
     */
    public static function removeParams(
        UriInterface $uri,
        string ...$keys
    ): UriInterface {
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
     * Add a parameter to an URI.
     *
     * @param UriInterface $uri
     *   The URI.
     * @param string $key
     *   The key of the parameter to add to the URI.
     * @param mixed $value
     *   The value of the parameter to add to the URI.
     * @param bool $force
     *   If true, overwrite any existing parameter from the URI.
     *
     * @throws TypeError
     * @throws InvalidArgumentException
     *
     * @return UriInterface
     *   A new URI with the added parameter.
     */
    public static function withParam(
        UriInterface $uri,
        string $key,
        mixed $value,
        bool $force = true
    ): UriInterface {
        $params = self::getParams($uri) + [$key => $value];

        if (true === $force) {
            $params[$key] = $value;
        }

        return $uri->withQuery(http_build_query($params));
    }

    /**
     * Add parameters to an URI.
     *
     * @param UriInterface $uri
     *   The URI.
     * @param string[] $params
     *   The set of parameters to add.
     * @param bool $force
     *   If true, overwrite any existing parameter from the URI.
     *
     * @throws TypeError
     * @throws InvalidArgumentException
     *
     * @return UriInterface
     *   A new URI with the added parameters.
     */
    public static function withParams(
        UriInterface $uri,
        array $params,
        bool $force = true
    ): UriInterface {
        // Reduce operation
        // Cannot use `array_reduce` because it
        // doesn't pass the key to the callback.
        foreach ($params as $key => $value) {
            $uri = self::withParam($uri, $key, $value, $force);
        }

        return $uri;
    }
}
