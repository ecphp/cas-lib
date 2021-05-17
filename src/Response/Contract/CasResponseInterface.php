<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Contract;

use Psr\Http\Message\ResponseInterface;

interface CasResponseInterface extends ResponseInterface
{
    public function getFormat(): string;

    public function normalize(): ResponseInterface;
}
