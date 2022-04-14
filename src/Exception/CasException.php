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

final class CasException extends Exception
{
    public static function errorWhileDoingRequest(Throwable $previous)
    {
        return new self('Error while doing request', 0, $previous);
    }

    public static function unableToAuthenticate()
    {
        return new self('Unable to authenticate the request.');
    }
}
