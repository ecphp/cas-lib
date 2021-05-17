<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\ProxyFailure as ProxyFailureInterface;
use Psr\Http\Message\StreamInterface;

final class ProxyFailure extends CasResponse implements ProxyFailureInterface
{
    public function getMessage(): string
    {
        return $this->parse()['serviceResponse']['proxyFailure'];
    }
}
