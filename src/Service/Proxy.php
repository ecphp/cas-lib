<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class Proxy extends Service implements ServiceInterface
{
    public function getCredentials(ResponseInterface $response): ResponseInterface
    {
        $introspect = $this->getIntrospector()->detect($response);

        if (false === ($introspect instanceof \EcPhp\CasLib\Introspection\Contract\Proxy)) {
            throw new Exception('Invalid response type.');
        }

        return $response;
    }

    protected function getProtocolProperties(RequestInterface $request): array
    {
        return $this->getProperties()['protocol']['proxy'] ?? [];
    }

    protected function getUri(RequestInterface $request): UriInterface
    {
        return $this->buildUri(
            $request->getUri(),
            'proxy',
            $this->formatProtocolParameters($this->getParameters($request))
        );
    }
}
