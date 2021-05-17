<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Utils\Uri;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class Proxy extends Service implements ServiceInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = $this->getParameters() + $this->getProtocolProperties()['default_parameters'] ?? [];

        $parameters += [
            'service' => (string) $request->getUri(),
            'ticket' => Uri::getParam($request->getUri(), 'ticket'),
        ];

        $response = $this
            ->getClient()
            ->sendRequest(
                $this
                    ->getRequestFactory()
                    ->createRequest(
                        'GET',
                        $this
                            ->buildUri(
                                $request->getUri(),
                                'proxy',
                                $this->formatProtocolParameters($parameters)
                            )
                    )
            );

        return $this->normalize($response, $parameters['format'] ?? 'XML');
    }

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
}
