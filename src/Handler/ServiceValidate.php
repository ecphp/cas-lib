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
use EcPhp\CasLib\Contract\Handler\ServiceValidateHandlerInterface;
use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate as TypeServiceValidate;
use EcPhp\CasLib\Exception\CasHandlerException;
use Ergebnis\Http\Method;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ServiceValidate extends Handler implements ServiceValidateHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $properties = $this->getProperties();

        $type = isset($properties['protocol'][HandlerInterface::TYPE_SERVICE_VALIDATE]['default_parameters']['pgtUrl'])
            ? HandlerInterface::TYPE_PROXY_VALIDATE
            : HandlerInterface::TYPE_SERVICE_VALIDATE;

        $parameters = $this->buildParameters(
            $this->getParameters(),
            $properties['protocol'][$type]['default_parameters'] ?? [],
            ['service' => (string) $request->getUri()],
        );

        $request = $this
            ->getPsr17()
            ->createRequest(
                Method::GET,
                $this
                    ->buildUri(
                        $request->getUri(),
                        $type,
                        $parameters
                    )
            );

        try {
            $response = $this
                ->getClient()
                ->sendRequest($request);
        } catch (Throwable $exception) {
            throw CasHandlerException::errorWhileDoingRequest($exception);
        }

        $response = $this->getCasResponseBuilder()->fromResponse($response);

        if ($response instanceof AuthenticationFailure) {
            throw CasHandlerException::authenticationFailure($response);
        }

        if (false === ($response instanceof TypeServiceValidate)) {
            throw CasHandlerException::serviceValidateValidationFailed($response);
        }

        if (HandlerInterface::TYPE_SERVICE_VALIDATE === $type) {
            return $response;
        }

        /** @var TypeServiceValidate $response */
        try {
            $proxyGrantingTicket = $response->getProxyGrantingTicket();
        } catch (Throwable $exception) {
            throw CasHandlerException::missingPGT($exception);
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
