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

use function sprintf;

final class CasResponseBuilderException extends Exception implements CasExceptionInterface
{
    public static function invalidResponseType(): self
    {
        return new self(
            'Invalid CAS response type.'
        );
    }

    public static function invalidStatusCode(int $code): self
    {
        return new self(
            sprintf('Unable to a CAS response with response status code: %s.', $code)
        );
    }

    public static function unknownResponseType(): self
    {
        return new self(
            'Unknown CAS response type.'
        );
    }
}
