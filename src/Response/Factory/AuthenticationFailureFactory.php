<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Factory;

use EcPhp\CasLib\Contract\Response\Factory\AuthenticationFailureFactory as AuthenticationFailureFactoryInterface;
use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure as AuthenticationFailureInterface;
use EcPhp\CasLib\Response\Type\AuthenticationFailure;
use Psr\Http\Message\ResponseInterface;

final class AuthenticationFailureFactory implements AuthenticationFailureFactoryInterface
{
    public function decorate(ResponseInterface $response): AuthenticationFailureInterface
    {
        return new AuthenticationFailure($response);
    }
}
