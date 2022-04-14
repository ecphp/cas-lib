<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\tests\EcPhp\CasLib\Handler;

use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Introspection\Introspector;
use Exception;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\HttpClient\Psr18Client;
use tests\EcPhp\CasLib\Handler\ProxyValidate;

class ProxyValidateSpec extends ObjectBehavior
{
    public function it_can_check_the_visibility_of_some_methods(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

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

        $this->beConstructedWith(
            [],
            $cache,
            $client,
            new Introspector(),
            CasSpecUtils::getTestProperties(),
            $psr17
        );

        $this
            ->getClient()
            ->shouldBeAnInstanceOf(ClientInterface::class);

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
        $request = new Request('GET', 'http://from/it_can_detect_when_no_credentials/error500');

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$request]);
    }

    public function it_can_parse_a_response(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            [],
            $cache,
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            new Introspector(),
            CasSpecUtils::getTestProperties(),
            $psr17
        );

        $body = <<< 'EOF'
                <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                <cas:authenticationSuccess>
                <cas:user>username</cas:user>
                </cas:authenticationSuccess>
                </cas:serviceResponse>
            EOF;

        $response = new Response(
            200,
            [],
            $body
        );
        $request = new Request(
            'GET',
            'http://from/it_can_parse_a_response'
        );

        $this
            ->parse($request, $response)
            ->shouldBeArray();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProxyValidate::class);
    }

    public function let(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            [],
            $cache,
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            new Introspector(),
            CasSpecUtils::getTestProperties(),
            $psr17
        );
    }
}
