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
use Psr\Http\Message\UriInterface;
use Throwable;

final class Proxy extends Service implements HandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->getClient()->sendRequest($request);
        } catch (Throwable $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        $introspect = $this->getIntrospector()->detect($this->normalize($request, $response));

        if (false === ($introspect instanceof \EcPhp\CasLib\Introspection\Contract\Proxy)) {
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
