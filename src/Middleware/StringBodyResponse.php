<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Middleware;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use loophp\psr17\Psr17Interface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class StringBodyResponse implements Plugin
{
    private Psr17Interface $psr17;

    public function __construct(Psr17Interface $psr17)
    {
        $this->psr17 = $psr17;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $psr17 = $this->psr17;

        return $next($request)
            ->then(
                static fn (ResponseInterface $response): ResponseInterface => $response->withBody($psr17->createStream((string) $response->getBody()))
            );
    }
}
