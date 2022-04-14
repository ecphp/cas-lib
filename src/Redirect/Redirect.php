<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Redirect;

use EcPhp\CasLib\Handler\Handler;
use Psr\Http\Message\ResponseInterface;

abstract class Redirect extends Handler
{
    protected function createRedirectResponse(string $url): ResponseInterface
    {
        return $this
            ->getPsr17()
            ->createResponse(302)
            ->withHeader('Location', $url);
    }
}
