<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Response\Contract\Proxy as ProxyInterface;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

final class Proxy extends CasResponse implements ProxyInterface
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

    public function getProxyTicket(): string
    {
        return $this->parse()['serviceResponse']['proxySuccess']['proxyTicket'];
    }

    public function withPgtIou(): ProxyInterface
    {
        $pgt = $this->parse()['serviceResponse']['authenticationSuccess']['proxyGrantingTicket'];

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
