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
use EcPhp\CasLib\Exception\CasHandlerException;
use EcPhp\CasLib\Utils\Uri;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ProxyCallback extends Handler implements HandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this
            ->getPsr17()
            ->createResponse();

        $parameters = $this->getParameters();
        $parameters += Uri::getParams($request->getUri());
        $parameters += (array) $request->getBody();
        $parameters += ['pgtId' => null, 'pgtIou' => null];

        if (null === $parameters['pgtId'] && null === $parameters['pgtIou']) {
            // We cannot return an exception here because prior sending the
            // PGT ID and PGTIOU, a request is made by the CAS server in order
            // to check the existence of the proxy callback endpoint.
            return $response;
        }

        if (null === $parameters['pgtIou']) {
            throw CasHandlerException::pgtIouIsNull();
        }

        if (null === $parameters['pgtId']) {
            throw CasHandlerException::pgtIdIsNull();
        }

        try {
            $cacheItem = $this
                ->getCache()
                ->getItem($parameters['pgtIou']);
        } catch (Throwable $exception) {
            throw CasHandlerException::getItemFromCacheFailure($exception);
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
