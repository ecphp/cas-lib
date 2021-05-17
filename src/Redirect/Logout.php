<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Redirect;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Logout extends Redirect implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createRedirectResponse(
            (string) $this->buildUri(
                $request->getUri(),
                'logout',
                $this->formatProtocolParameters($this->getParameters())
            )
        );
    }

    protected function getProtocolProperties(): array
    {
        return $this->getProperties()['protocol']['logout'] ?? [];
    }
}
