<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Contract\Handler\HandlerInterface;
use EcPhp\CasLib\Utils\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Logout extends Handler implements HandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        $parameters = $this->getParameters();
        $parameters += Uri::getParams($request->getUri());
        $parameters += $this->getProperties()['protocol'][HandlerInterface::TYPE_LOGOUT]['default_parameters'] ?? [];

        $uri = $this
            ->buildUri(
                $request->getUri(),
                HandlerInterface::TYPE_LOGOUT,
                $this->formatProtocolParameters($parameters)
            );

        return $this
            ->getPsr17()
            ->createResponse(302)
            ->withHeader(
                'Location',
                (string) $uri
            );
    }
}
