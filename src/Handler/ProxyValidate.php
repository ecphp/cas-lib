<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Utils\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class ProxyValidate extends Service implements ServiceValidateHandlerInterface
{
    protected function getProtocolProperties(UriInterface $uri): array
    {
        $protocolProperties = $this->getProperties()['protocol']['proxyValidate'] ?? [];

        $protocolProperties['default_parameters'] += [
            'service' => (string) $uri,
            'ticket' => Uri::getParam($uri, 'ticket'),
        ];

        return $protocolProperties;
    }

    protected function getUri(RequestInterface $request): UriInterface
    {
        return $this
            ->buildUri(
                $request->getUri(),
                'proxyValidate',
                $this->formatProtocolParameters($this->getParameters($request))
            );
    }
}
