<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract\Response;

use Psr\Http\Message\ResponseInterface;

interface CasResponseInterface extends ResponseInterface
{
    public function toArray(): array;
}
