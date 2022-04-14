<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use Psr\Http\Message\ResponseInterface;

interface HandlerInterface
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(): ?ResponseInterface;
}
