<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Handler;

use EcPhp\CasLib\Handler\ServiceValidate;
use EcPhp\CasLib\Response\CasResponseBuilder;
use Ergebnis\Http\Method;
use Exception;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use spec\EcPhp\CasLib\Cas;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\HttpClient\Psr18Client;

class ServiceValidateSpec extends ObjectBehavior
{
    public function it_can_detect_when_no_credentials()
    {
        $request = new ServerRequest(Method::GET, 'http://from');

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$request]);
    }

    public function it_can_get_credentials_with_pgtUrl(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

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

        $this->beConstructedWith(
            [],
            $cache,
            new CasResponseBuilder(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            Cas::getTestPropertiesWithPgtUrl(),
            $psr17
        );

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_get_credentials_with_pgtUrl')
        );

        $this
            ->handle($request)
            ->shouldImplement(ResponseInterface::class);
    }

    public function it_can_get_credentials_without_pgtUrl()
    {
        $request = new ServerRequest(
            Method::GET,
            'http://from/it_can_get_credentials_without_pgtUrl'
        );

        $this
            ->handle($request)
            ->shouldImplement(ResponseInterface::class);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ServiceValidate::class);
    }

    public function let(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            [],
            $cache,
            new CasResponseBuilder(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            Cas::getTestProperties(),
            $psr17
        );
    }
}
