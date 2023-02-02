<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Redirect;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class Logout extends Redirect implements RedirectInterface
{
    public function handle(): ?ResponseInterface
    {
        return $this->createRedirectResponse((string) $this->getUri());
    }

    protected function getProtocolProperties(): array
    {
        return $this->getProperties()['protocol']['logout'] ?? [];
    }

    private function getUri(): UriInterface
    {
        $serverRequest = $this->getServerRequest()->getUri();

        $parameters = $this->buildParameters(
            $this->getParameters(),
            $this->getProtocolProperties()['default_parameters'] ?? [],
            ['service' => (string) $serverRequest],
        );

        return $this->buildUri($serverRequest, 'logout', $parameters);
    }
}
