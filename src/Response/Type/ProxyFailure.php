<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Type;

use EcPhp\CasLib\Contract\Response\Type\ProxyFailure as ProxyFailureInterface;

final class ProxyFailure extends CasResponse implements ProxyFailureInterface
{
    public function getMessage(): string
    {
        return $this->toArray()['serviceResponse']['proxyFailure'];
    }
}
