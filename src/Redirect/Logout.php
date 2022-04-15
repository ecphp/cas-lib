<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Redirect;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class Logout extends Redirect implements RedirectInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->createRedirectResponse($this->getUri($request));
    }

    protected function getProtocolProperties(UriInterface $uri): array
    {
        return $this->getProperties()['protocol']['logout'] ?? [];
    }

    private function getUri(RequestInterface $request): UriInterface
    {
        return $this->buildUri(
            $request->getUri(),
            'logout',
            $this->formatProtocolParameters($this->getParameters($request))
        );
    }
}
