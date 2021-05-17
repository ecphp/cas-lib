<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\ProxyServiceValidate as ProxyServiceValidateInterface;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

final class ProxyServiceValidate extends CasResponse implements ProxyServiceValidateInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(ResponseInterface $response, string $format, CacheItemPoolInterface $cache, StreamFactoryInterface $streamFactory, LoggerInterface $logger)
    {
        parent::__construct($response, $format, $streamFactory, $logger);
        $this->cache = $cache;
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    public function getCredentials(): array
    {
        return $this->parse()['serviceResponse']['authenticationSuccess'];
    }

    public function getProxies(): array
    {
        $hasProxy = isset($this->parse()['serviceResponse']['authenticationSuccess']['proxies']);

        return true === $hasProxy ?
            $this->parse()['serviceResponse']['authenticationSuccess']['proxies'] :
            [];
    }

    public function withPgtIou(): ProxyServiceValidateInterface
    {
        $parsedResponse = $this->parse();

        $proxyGrantingTicket = array_key_exists(
            'proxyGrantingTicket',
            $parsedResponse['serviceResponse']['authenticationSuccess']
        );

        if (false === $proxyGrantingTicket) {
            return $this;
        }

        $pgt = $parsedResponse['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

        if (false === $this->getCache()->hasItem($pgt)) {
            throw new Exception('CAS validation failed: pgtIou not found in the cache.');
        }

        return $this
            ->withBody(
                $this
                    ->getStreamFactory()
                    ->createStream(
                        str_replace(
                            $pgt,
                            $this->getCache()->getItem($pgt)->get(),
                            (string) $this->getBody()
                        )
                    )
            );
    }

}
