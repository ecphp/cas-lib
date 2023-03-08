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
use EcPhp\CasLib\Contract\Response\Type\Proxy;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function get_class;

final class CasHandlerException extends Exception implements CasExceptionInterface
{
    public static function authenticationFailure(AuthenticationFailure $response): self
    {
        return new self(
            sprintf('CAS authentication failure: %s', (string) $response->getBody())
        );
    }

    public static function errorWhileDoingRequest(Throwable $previous): self
    {
        $exception = CasException::errorWhileDoingRequest($previous);

        return new self($exception->getMessage(), 0, $exception);
    }

    public static function getItemFromCacheFailure(Throwable $exception): self
    {
        return new self(
            sprintf('Unable to get item from cache: %s', $exception->getMessage()),
            0,
            $exception
        );
    }

    public static function invalidProxyResponseType(ResponseInterface $response): self
    {
        return new self(
            sprintf(
                'CAS proxy failure: Invalid response type, %s given while expecting %s.',
                get_class($response),
                Proxy::class
            )
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

    public static function missingPGT(Throwable $exception): self
    {
        return new self(
            $exception->getMessage(),
            0,
            $exception
        );
    }

    public static function pgtIdIsNull(): self
    {
        return new self(
            'CAS proxy callback failure: PGT ID is null'
        );
    }

    public static function pgtIouIsNull(): self
    {
        return new self(
            'CAS proxy callback failure: PGT IOU is null'
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
            sprintf(
                'CAS service validation failure: Invalid response type, %s given while expecting %s.',
                get_class($response),
                ServiceValidate::class
            )
        );
    }

    public static function unableToSaveItemInCache(): self
    {
        return new self(
            'CAS Proxy callback failure. The cache service was unable to save the PGT ID.'
        );
    }
}
