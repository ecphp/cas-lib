<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Exception;

use Exception;
use Throwable;

use function sprintf;

final class CasException extends Exception implements CasExceptionInterface
{
    public static function emptyResponseBodyFailure(): self
    {
        return new self(
            'Response body is empty.'
        );
    }

    public static function errorWhileDoingRequest(Throwable $previous): self
    {
        return new self(
            sprintf('Error while doing request: %s', $previous->getMessage()),
            0,
            $previous
        );
    }

    public static function missingResponseContentTypeHeader(): self
    {
        return new self(
            'Missing "Content-Type" header, unable to detect response format.'
        );
    }

    public static function unableToAuthenticate(Throwable $previous): self
    {
        return new self(
            sprintf('Authentication failure: %s', $previous->getMessage()),
            0,
            $previous
        );
    }

    public static function unableToConvertResponse(string $header, Throwable $previous): self
    {
        return new self(
            sprintf('Unable to convert response (%s) to array.', $header),
            0,
            $previous
        );
    }

    public static function unsupportedRequest(): self
    {
        return new self('The request does not support CAS authentication.');
    }

    public static function unsupportedResponseFormat(string $format): self
    {
        return new self(
            sprintf('Unsupported response format: %s', $format)
        );
    }
}
