<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Factory;

use EcPhp\CasLib\Contract\Response\Factory\ProxyFactory as ProxyFactoryInterface;
use EcPhp\CasLib\Contract\Response\Type\Proxy as ProxyInterface;
use EcPhp\CasLib\Response\Type\Proxy;
use Psr\Http\Message\ResponseInterface;

final class ProxyFactory implements ProxyFactoryInterface
{
    public function decorate(ResponseInterface $response): ProxyInterface
    {
        return new Proxy($response);
    }
}
