<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Handler\HandlerInterface;
use Psr\Http\Message\ResponseInterface;

interface ServiceInterface extends HandlerInterface
{
    public function getCredentials(ResponseInterface $response): ?ResponseInterface;
}
