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
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;

abstract class Service extends Handler
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->getClient()->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw CasException::errorWhileDoingRequest($exception);
        }

        $response = $this->getCasResponseBuilder()->fromResponse($response);

        if (false === ($response instanceof TypeServiceValidate)) {
            throw new Exception('CAS Service validation failed.');
        }

        $parsedResponse = $response->toArray();

        $proxyGrantingTicket = array_key_exists(
            'proxyGrantingTicket',
            $parsedResponse['serviceResponse']['authenticationSuccess']
        );

        if (false === $proxyGrantingTicket) {
            return $response;
        }

        $body = json_encode(
            $this->updateParsedResponseWithPgt($parsedResponse)
        );

        if (false === $body) {
            throw new Exception('Unable to JSON encode the body');
        }

        return $response
            ->withBody($this->getPsr17()->createStream($body))
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get the URI.
     */
    abstract protected function getUri(RequestInterface $request): UriInterface;

    /**
     * @param array[] $response
     *
     * @return array[]|null
     */
    protected function updateParsedResponseWithPgt(array $response): array
    {
        $pgt = $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

        $hasPgtIou = $this
            ->getCache()
            ->hasItem($pgt);

        if (false === $hasPgtIou) {
            throw new Exception('CAS validation failed: pgtIou not found in the cache.');
        }

        $response['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'] = $this
            ->getCache()
            ->getItem($pgt)
            ->get();

        return $response;
    }
}
