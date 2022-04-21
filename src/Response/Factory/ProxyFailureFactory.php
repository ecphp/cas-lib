<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Factory;

use EcPhp\CasLib\Contract\Response\Factory\ProxyFailureFactory as ProxyFailureFactoryInterface;
use EcPhp\CasLib\Contract\Response\Type\ProxyFailure as ProxyFailureInterface;
use EcPhp\CasLib\Response\Type\ProxyFailure;
use Psr\Http\Message\ResponseInterface;

final class ProxyFailureFactory implements ProxyFailureFactoryInterface
{
    public function decorate(ResponseInterface $response): ProxyFailureInterface
    {
        return new ProxyFailure($response);
    }
}
