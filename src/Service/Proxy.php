<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class Proxy extends Service implements ServiceInterface
{
    public function getCredentials(ResponseInterface $response): ?ResponseInterface
    {
        try {
            $introspect = $this->getIntrospector()->detect($response);
        } catch (InvalidArgumentException $exception) {
            $this
                ->getLogger()
                ->error($exception->getMessage());

            return null;
        }

        if (false === ($introspect instanceof \EcPhp\CasLib\Introspection\Contract\Proxy)) {
            return null;
        }

        return $response;
    }

    protected function getProtocolProperties(): array
    {
        return $this->getProperties()['protocol']['proxy'] ?? [];
    }

    protected function getUri(): UriInterface
    {
        $parameters = $this->buildParameters(
            $this->getParameters(),
            $this->getProtocolProperties()['default_parameters'] ?? []
        );

        return $this->buildUri(
            $this->getServerRequest()->getUri(),
            'proxy',
            $parameters
        );
    }
}
