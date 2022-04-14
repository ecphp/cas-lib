<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Exception\CasException;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

final class Proxy extends Service implements HandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->getClient()->sendRequest($request);
        } catch (Throwable $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        $response = $this->getCasResponseBuilder()->fromResponse($response);

        if (false === ($response instanceof \EcPhp\CasLib\Contract\Response\Type\Proxy)) {
            throw new Exception('Invalid response type.');
        }

        return $response;
    }

    protected function getProtocolProperties(RequestInterface $request): array
    {
        return $this->getProperties()['protocol']['proxy'] ?? [];
    }

    protected function getUri(RequestInterface $request): UriInterface
    {
        return $this
            ->buildUri(
                $request->getUri(),
                'proxy',
                $this->formatProtocolParameters($this->getParameters($request))
            );
    }
}
