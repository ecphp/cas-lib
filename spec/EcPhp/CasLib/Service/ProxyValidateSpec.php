<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Service;

use EcPhp\CasLib\Introspection\Introspector;
use EcPhp\CasLib\Service\ProxyValidate;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\HttpClient\Psr18Client;

class ProxyValidateSpec extends ObjectBehavior
{
    public function it_can_detect_when_no_credentials()
    {
        $response = new Response(500);

        $this
            ->getCredentials($response)
            ->shouldBeNull();
    }

    public function it_can_get_credentials_with_pgtUrl(ServerRequestInterface $serverRequest, ClientInterface $client, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $serverRequest = new ServerRequest('GET', 'http://from');
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem
            ->set('pgtId')
            ->willReturn($cacheItem);

        $cacheItem
            ->expiresAfter(300)
            ->willReturn($cacheItem);

        $cacheItem
            ->get()
            ->willReturn('pgtIou');

        $cache
            ->save($cacheItem)
            ->willReturn(true);

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $this->beConstructedWith($serverRequest, ['service' => 'service', 'ticket' => 'ST-ticket-pgt'], Cas::getTestPropertiesWithPgtUrl(), $client, $psr17, $cache, $logger, new Introspector());

        $response = $this->handle();

        $response
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $this
            ->getCredentials($response->getWrappedObject())
            ->shouldImplement(ResponseInterface::class);

        $logger
            ->debug('Proxy validation service successful.')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_get_credentials_without_pgtUrl(ServerRequestInterface $serverRequest, ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $serverRequest = new ServerRequest('GET', 'http://from');
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $this->beConstructedWith($serverRequest, ['service' => 'service', 'ticket' => 'ticket'], Cas::getTestProperties(), $client, $psr17, $cache, $logger, new Introspector());

        $response = $this->handle();

        $response
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $this
            ->getCredentials($response->getWrappedObject())
            ->shouldImplement(ResponseInterface::class);

        $logger
            ->debug('Service validation service successful.')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_is_initializable(ServerRequestInterface $serverRequest, ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $this->shouldHaveType(ProxyValidate::class);
    }

    public function let(ServerRequestInterface $serverRequest, ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith($serverRequest, [], Cas::getTestProperties(), $client, $psr17, $cache, $logger, new Introspector());
    }
}
