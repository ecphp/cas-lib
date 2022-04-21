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
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Utils\Uri;
use Ergebnis\Http\Method;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class Proxy extends Handler implements HandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $properties = $this->getProperties();

        $parameters = $this->getParameters();
        $parameters += Uri::getParams($request->getUri());
        $parameters += $properties['protocol'][HandlerInterface::TYPE_PROXY]['default_parameters'] ?? [];
        $parameters += ['service' => (string) $request->getUri()];

        $uri = $this->buildUri(
            $request->getUri(),
            HandlerInterface::TYPE_PROXY,
            $this->formatProtocolParameters($parameters)
        );

        $request = $this
            ->getPsr17()
            ->createRequest(
                Method::GET,
                $uri
            );

        try {
            $response = $this
                ->getClient()
                ->sendRequest($request);
        } catch (Throwable $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        $response = $this->getCasResponseBuilder()->fromResponse($response);

        if (false === ($response instanceof \EcPhp\CasLib\Contract\Response\Type\Proxy)) {
            throw new Exception('Invalid response type.');
        }

        return $response;
    }
}
