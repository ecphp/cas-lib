<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\tests\EcPhp\CasLib\Service;

use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Introspection\Introspector;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;
use Symfony\Component\HttpClient\Psr18Client;
use tests\EcPhp\CasLib\Service\ProxyValidate;

class ProxyValidateSpec extends ObjectBehavior
{
    public function it_can_check_the_visibility_of_some_methods(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $client = new Psr18Client(Cas::getHttpClientMock());

        $cacheItem
            ->set('pgtId')
            ->willReturn($cacheItem);

        $cacheItem
            ->expiresAfter(300)
            ->willReturn($cacheItem);

        $cacheItem
            ->get()
            ->willReturn('pgtId');

        $cache
            ->save($cacheItem)
            ->willReturn(true);

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $this->beConstructedWith([], Cas::getTestProperties(), $client, $psr17, $cache, $logger, new Introspector());

        $this
            ->getClient()
            ->shouldBeAnInstanceOf(ClientInterface::class);

        $this
            ->getLogger()
            ->shouldBeAnInstanceOf(LoggerInterface::class);

        $this
            ->getCache()
            ->shouldBeAnInstanceOf(CacheItemPoolInterface::class);

        $this
            ->getIntrospector()
            ->shouldBeAnInstanceOf(IntrospectorInterface::class);

        $response = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'proxyGrantingTicket' => 'pgtIou',
                ],
            ],
        ];

        $this
            ->updateParsedResponseWithPgt($response)
            ->shouldReturn(
                [
                    'serviceResponse' => [
                        'authenticationSuccess' => [
                            'proxyGrantingTicket' => 'pgtId',
                        ],
                    ],
                ]
            );
    }

    public function it_can_detect_when_no_credentials()
    {
        $response = new Response(500);

        $this
            ->getCredentials($response)
            ->shouldBeNull();
    }

    public function it_can_log_debugging_information_when_trying_to_get_unexisting_pgtIou(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $client = new Psr18Client(Cas::getHttpClientMock());

        $cache
            ->hasItem('pgtIou')
            ->willReturn(false);

        $this->beConstructedWith([], Cas::getTestProperties(), $client, $psr17, $cache, $logger, new Introspector());

        $response = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'proxyGrantingTicket' => 'pgtIou',
                ],
            ],
        ];

        $this
            ->updateParsedResponseWithPgt($response)
            ->shouldReturn(null);

        $logger
            ->error('CAS validation failed: pgtIou not found in the cache.', ['pgtIou' => 'pgtIou'])
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_parse_a_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $client = new Psr18Client(Cas::getHttpClientMock());

        $this->beConstructedWith([], Cas::getTestProperties(), $client, $psr17, $cache, $logger, new Introspector());

        $response = new Response(200, [], 'foo');
        $request = new Request(
            'GET',
            new Uri('http://from/it_can_parse_a_response')
        );

        $this
            ->parse($request, $response)
            ->shouldBeArray();

        $logger
            ->error('Unable to parse the response with the specified format {format}.', ['format' => 'XML', 'response' => 'foo'])
            ->shouldHaveBeenCalledOnce();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProxyValidate::class);
    }

    public function let(ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith([], Cas::getTestProperties(), $client, $psr17, $cache, $logger, new Introspector());
    }
}
