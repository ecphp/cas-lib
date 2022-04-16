<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Contract\Handler\ServiceValidateHandlerInterface;
use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate as TypeServiceValidate;
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Exception\CasHandlerException;
use EcPhp\CasLib\Utils\Uri;
use Ergebnis\Http\Method;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ServiceValidate extends Handler implements ServiceValidateHandlerInterface
{
    private const PROXYVALIDATE = 'proxyValidate';

    private const SERVICEVALIDATE = 'serviceValidate';

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $properties = $this->getProperties();

        $type = $properties['protocol']['serviceValidate']['default_parameters']['pgtUrl'] ?? false
            ? self::PROXYVALIDATE
            : self::SERVICEVALIDATE;

        $parameters = ['service' => (string) $request->getUri()]
            + Uri::getParams($request->getUri())
            + ($properties['protocol'][$type]['default_parameters'] ?? []);

        $request = $this
            ->getPsr17()
            ->createRequest(
                Method::GET,
                $this
                    ->buildUri(
                        $request->getUri(),
                        $type,
                        $this->formatProtocolParameters($parameters)
                    )
            );

        try {
            $response = $this
                ->getClient()
                ->sendRequest($request);
        } catch (Throwable $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        $response = $this->getCasResponseBuilder()->fromResponse($response);

        if ($response instanceof AuthenticationFailure) {
            throw CasHandlerException::authenticationFailure($response);
        }

        if (false === ($response instanceof TypeServiceValidate)) {
            throw CasHandlerException::serviceValidateValidationFailed($response);
        }

        if (self::SERVICEVALIDATE === $type) {
            return $response;
        }

        try {
            /** @var TypeServiceValidate $response */
            $proxyGrantingTicket = $response->getProxyGrantingTicket();
        } catch (Throwable $exception) {
            return $response;
        }

        $hasPgtIou = $this
            ->getCache()
            ->hasItem($proxyGrantingTicket);

        if (false === $hasPgtIou) {
            throw CasHandlerException::serviceValidatePGTNotFound();
        }

        try {
            $pgtId = $this
                ->getCache()
                ->getItem($proxyGrantingTicket);
        } catch (Throwable $exception) {
            throw CasHandlerException::serviceValidateUnableToGetPGTFromCache($exception);
        }

        if (null === $pgtId->get()) {
            throw CasHandlerException::serviceValidateInvalidPGTIdValue();
        }

        return $response
            ->withBody(
                $this
                    ->getPsr17()
                    ->createStream(
                        str_replace(
                            $proxyGrantingTicket,
                            $pgtId->get(),
                            (string) $response->getBody()
                        )
                    )
            );
    }
}
