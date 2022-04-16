<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Exception;

use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class CasHandlerException extends Exception implements CasExceptionInterface
{
    public static function authenticationFailure(AuthenticationFailure $response): self
    {
        return new self(
            sprintf('CAS authentication failure: %s', (string) $response->getBody())
        );
    }

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

    public static function serviceValidateInvalidPGTIdValue(): self
    {
        return new self(
            'CAS service validation failed: Invalid PGT ID value.'
        );
    }

    public static function serviceValidatePGTNotFound(): self
    {
        return new self(
            'CAS service validation failed: ProxyGrantingTicket not found.'
        );
    }

    public static function serviceValidateUnableToGetPGTFromCache(Throwable $exception): self
    {
        return new self(
            sprintf('CAS service validation failed: Unable to get PGT (%s).', $exception->getMessage()),
            0,
            $exception
        );
    }

    public static function serviceValidateValidationFailed(ResponseInterface $response): self
    {
        return new self(
            sprintf('CAS service validation failed: %s', (string) $response->getBody())
        );
    }
}
