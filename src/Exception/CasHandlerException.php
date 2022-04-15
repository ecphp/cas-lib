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

final class CasHandlerException extends Exception implements CasExceptionInterface
{
    public static function loginInvalidParameters(): self
    {
        return new self(
            'Login parameters are invalid.'
        );
    }

    public static function loginRenewAndGatewayParametersAreSet(): self
    {
        return new self(
            'Unable to get the Login response, gateway and renew parameter cannot be set together.'
        );
    }
}
