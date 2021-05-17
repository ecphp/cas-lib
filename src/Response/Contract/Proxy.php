<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Contract;

interface Proxy extends CasResponseInterface
{
    public function getProxyTicket(): string;

    public function withPgtIou(): Proxy;
}