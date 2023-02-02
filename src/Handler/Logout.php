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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Logout extends Handler implements HandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        $parameters = $this->buildParameters(
            $this->getParameters(),
            $this->getProperties()['protocol'][HandlerInterface::TYPE_LOGOUT]['default_parameters'] ?? [],
            ['service' => (string) $request->getUri()],
        );

        return $this
            ->getPsr17()
            ->createResponse(302)
            ->withHeader(
                'Location',
                (string) $this
                    ->buildUri(
                        $request->getUri(),
                        HandlerInterface::TYPE_LOGOUT,
                        $parameters
                    )
            );
    }
}
