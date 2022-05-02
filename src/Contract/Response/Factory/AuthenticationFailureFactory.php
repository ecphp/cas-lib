<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract\Response\Factory;

use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure;
use Psr\Http\Message\ResponseInterface;

interface AuthenticationFailureFactory
{
    public function decorate(ResponseInterface $response): AuthenticationFailure;
}
