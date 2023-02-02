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

final class ProxyValidate extends Service implements ServiceInterface
{
    protected function getProtocolProperties(): array
    {
        return $this->getProperties()['protocol']['proxyValidate'] ?? [];
    }

    protected function getUri(): UriInterface
    {
        $parameters = $this->buildParameters(
            $this->getParameters(),
            [
                'service' => (string) $this->getServerRequest()->getUri(),
                'ticket' => Uri::getParam($this->getServerRequest()->getUri(), 'ticket'),
            ],
            $this->getProtocolProperties()['default_parameters'] ?? []
        );

        return $this->buildUri(
            $this->getServerRequest()->getUri(),
            'proxyValidate',
            $parameters
        );
    }
}
