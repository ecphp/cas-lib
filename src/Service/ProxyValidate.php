<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Utils\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Class ProxyValidate.
 */
final class ProxyValidate extends Service implements ServiceInterface
{
    protected function getProtocolProperties(): array
    {
        $protocolProperties = $this->getProperties()['protocol']['proxyValidate'] ?? [];

        $protocolProperties['default_parameters'] += [
            'service' => (string) $this->getServerRequest()->getUri(),
            'ticket' => Uri::getParam($this->getServerRequest()->getUri(), 'ticket'),
        ];

        return $protocolProperties;
    }

    protected function getUri(): UriInterface
    {
        return $this->buildUri(
            $this->getServerRequest()->getUri(),
            'proxyValidate',
            $this->formatProtocolParameters($this->getParameters())
        );
    }
}
