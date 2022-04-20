<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract\Response;

use EcPhp\CasLib\Exception\CasResponseBuilderException;
use Psr\Http\Message\ResponseInterface;

interface CasResponseBuilderInterface
{
    /**
     * @throws CasResponseBuilderException
     */
    public function fromResponse(ResponseInterface $response): CasResponseInterface;
}
