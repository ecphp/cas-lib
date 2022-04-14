<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Contract\Response\CasResponseInterface;
use Psr\Http\Message\ResponseInterface;

interface CasResponseBuilderInterface
{
    public function fromResponse(ResponseInterface $response): CasResponseInterface;
}
