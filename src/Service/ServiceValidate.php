<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Response\ServiceValidate as ResponseServiceValidate;
use EcPhp\CasLib\Utils\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServiceValidate extends Service implements ServiceInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $format = $parameters['format'] ?? 'XML';

        $parameters = $this->getParameters() + $this->getProtocolProperties()['default_parameters'] ?? [];

        $parameters += [
            'service' => (string) $request->getUri(),
            'ticket' => Uri::getParam($request->getUri(), 'ticket'),
        ];

        $response = new ResponseServiceValidate(
            $this
                ->getClient()
                ->sendRequest(
                    $this
                        ->getRequestFactory()
                        ->createRequest(
                            'GET',
                            $this
                            ->buildUri(
                                $request->getUri(),
                                'serviceValidate',
                                $this->formatProtocolParameters($parameters)
                            )
                        )
                ),
            $format,
            $this->getCache(),
            $this->getStreamFactory(),
            $this->getLogger()
        );

        return $response->normalize();
    }

    protected function getProtocolProperties(): array
    {
        return $this->getProperties()['protocol']['serviceValidate'] ?? [];
    }
}
