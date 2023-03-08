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

use function array_key_exists;

final class ProxyCallback extends Handler implements HandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = $this->buildParameters(
            $this->getParameters(),
            Uri::getParams($request->getUri()),
        );

        $response = $this
            ->getPsr17()
            ->createResponse();

        $hasPgtId = array_key_exists('pgtId', $parameters);
        $hasPgtIou = array_key_exists('pgtIou', $parameters);

        if (false === $hasPgtId && false === $hasPgtIou) {
            // We cannot return an exception here because prior sending the
            // PGT ID and PGTIOU, a request is made by the CAS server in order
            // to check the existence of the proxy callback endpoint.
            return $response;
        }

        if (false === $hasPgtIou) {
            throw CasHandlerException::pgtIouIsNull();
        }

        if (false === $hasPgtId) {
            throw CasHandlerException::pgtIdIsNull();
        }

        try {
            $cacheItem = $this
                ->getCache()
                ->getItem($parameters['pgtIou']);
        } catch (Throwable $exception) {
            throw CasHandlerException::getItemFromCacheFailure($exception);
        }

        $isSaved = $this
            ->getCache()
            ->save(
                $cacheItem
                    ->set($parameters['pgtId'])
                    ->expiresAfter(300)
            );

        if (false === $isSaved) {
            throw CasHandlerException::unableToSaveItemInCache();
        }

        return $response;
    }
}
