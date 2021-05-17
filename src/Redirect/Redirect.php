<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Redirect;

use EcPhp\CasLib\Handler\Handler;
use Psr\Http\Message\ResponseInterface;

abstract class Redirect extends Handler
{
    protected function createRedirectResponse(string $url): ResponseInterface
    {
        $this
            ->getLogger()
            ->debug('Building service response redirection to {url}.', ['url' => $url]);

        return $this
            ->getResponseFactory()
            ->createResponse(302)
            ->withHeader('Location', $url);
    }
}
