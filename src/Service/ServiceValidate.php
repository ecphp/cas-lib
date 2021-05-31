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
 * Class ServiceValidate.
 */
final class ServiceValidate extends Service implements ServiceInterface
{
    protected function getProtocolProperties(): array
    {
        $protocolProperties = $this->getProperties()['protocol']['serviceValidate'] ?? [];

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
            'serviceValidate',
            $this->formatProtocolParameters($this->getParameters())
        );
    }
}
