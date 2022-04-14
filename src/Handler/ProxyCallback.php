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
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class ProxyCallback extends Handler implements HandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        $response = $this
            ->getPsr17()
            ->createResponse(200);

        // POST parameters prevails over GET parameters.
        $parameters = $this->getParameters($request) +
            (array) $request->getBody() +
            Uri::getParams($request->getUri()) +
            ['pgtId' => null, 'pgtIou' => null];

        if (null === $parameters['pgtId'] && null === $parameters['pgtIou']) {
            return $response;
        }

        if (null === $parameters['pgtIou']) {
            return $response->withStatus(500);
        }

        if (null === $parameters['pgtId']) {
            return $response->withStatus(500);
        }

        try {
            $cacheItem = $this
                ->getCache()
                ->getItem($parameters['pgtIou']);
        } catch (Throwable $exception) {
            throw new Exception('Unable to get item from cache.', 0, $exception);
        }

        $this
            ->getCache()
            ->save(
                $cacheItem
                    ->set($parameters['pgtId'])
                    ->expiresAfter(300)
            );

        return $response;
    }
}
