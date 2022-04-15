<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Contract\Response\Type\ServiceValidate as TypeServiceValidate;
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Exception\CasHandlerException;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ServiceValidate extends Handler implements ServiceValidateHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->getClient()->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        $response = $this->getCasResponseBuilder()->fromResponse($response);

        if (false === ($response instanceof TypeServiceValidate)) {
            throw CasHandlerException::serviceValidateValidationFailed();
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
            throw new Exception('PGT not found in the cache.');
        }

        try {
            $pgtId = $this
                ->getCache()
                ->getItem($proxyGrantingTicket);
        } catch (Throwable $exception) {
            throw new Exception('Unable to get PGT from cache', 0, $exception);
        }

        if (null === $pgtId->get()) {
            throw new Exception('Invalid PGT ID value.');
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
