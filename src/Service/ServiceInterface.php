<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface ServiceInterface extends RequestHandlerInterface
{
    public function getCredentials(ResponseInterface $response): ?ResponseInterface;
}
