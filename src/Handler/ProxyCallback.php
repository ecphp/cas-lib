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
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ProxyCallback extends Handler implements HandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = $this->getParameters();
        $parameters += Uri::getParams($request->getUri());
        $parameters += (array) $request->getBody();
        $parameters += ['pgtId' => null, 'pgtIou' => null];

        if (null === $parameters['pgtId'] && null === $parameters['pgtIou']) {
            throw new Exception('No PGT ID or PGT IOU.');
        }

        if (null === $parameters['pgtIou']) {
            throw new Exception('PGT IOU is null.');
        }

        if (null === $parameters['pgtId']) {
            throw new Exception('PGT ID is null.');
        }

        try {
            $cacheItem = $this
                ->getCache()
                ->getItem($parameters['pgtIou']);
        } catch (Throwable $exception) {
            throw new Exception(
                'Unable to get item from cache.',
                0,
                $exception
            );
        }

        $this
            ->getCache()
            ->save(
                $cacheItem
                    ->set($parameters['pgtId'])
                    ->expiresAfter(300)
            );

        return $this
            ->getPsr17()
            ->createResponse();
    }
}
