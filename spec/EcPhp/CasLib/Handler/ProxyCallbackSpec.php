<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Handler;

use EcPhp\CasLib\Handler\ProxyCallback;
use EcPhp\CasLib\Introspection\Contract\IntrospectorInterface;
use EcPhp\CasLib\Utils\Uri as UtilsUri;
use Exception;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use spec\EcPhp\CasLib\Cas;

class ProxyCallbackSpec extends ObjectBehavior
{
    public function it_can_catch_issue_with_the_cache(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, ClientInterface $client, IntrospectorInterface $introspector)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from/it_can_catch_issue_with_the_cache'),
                [
                    'pgtId' => 'pgtId',
                    'pgtIou' => 'pgtIou',
                ]
            )
        );

        $cacheItem
            ->set('pgtId')
            ->willReturn($cacheItem);

        $cacheItem
            ->expiresAfter(300)
            ->willReturn($cacheItem);

        $uniqid = uniqid('ErrorMessageHere', true);

        $cache
            ->getItem('pgtIou')
            ->willThrow(new Exception($uniqid));

        $cache
            ->save($cacheItem)
            ->willReturn(true);

        $this->beConstructedWith([], $cache, $client, $introspector, Cas::getTestProperties(), $psr17);

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$request]);
    }

    public function it_can_test_if_the_cache_is_working(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from/it_can_test_if_the_cache_is_working'),
                [
                    'pgtId' => 'pgtId',
                    'pgtIou' => 'pgtIou',
                ]
            )
        );

        $this
            ->handle($request);

        $cache
            ->save($cacheItem)
            ->shouldHaveBeenCalledOnce();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProxyCallback::class);
    }

    public function let(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, ClientInterface $client, IntrospectorInterface $introspector)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $cacheItem
            ->set('pgtId')
            ->willReturn($cacheItem);

        $cacheItem
            ->expiresAfter(300)
            ->willReturn($cacheItem);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $cache
            ->save($cacheItem)
            ->willReturn(true);

        $this->beConstructedWith([], $cache, $client, $introspector, Cas::getTestProperties(), $psr17);
    }
}
