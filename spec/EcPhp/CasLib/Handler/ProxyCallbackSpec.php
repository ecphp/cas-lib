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
use EcPhp\CasLib\Utils\Uri as UtilsUri;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;

class ProxyCallbackSpec extends ObjectBehavior
{
    public function it_can_catch_issue_with_the_cache(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

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

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->handle($request);

        $logger
            ->error($uniqid)
            ->shouldHaveBeenCalledOnce();
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

    public function it_can_test_the_logger_when_missing_pgtId(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from/it_can_test_the_logger_when_missing_pgtId'),
                [
                    'pgtIou' => 'pgtIou',
                ]
            )
        );

        $this
            ->handle($request);

        $logger
            ->debug('Missing proxy callback parameter (pgtId).')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_test_the_logger_when_missing_pgtIou(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from/it_can_test_the_logger_when_missing_pgtIou'),
                [
                    'pgtId' => 'pgtId',
                ]
            )
        );

        $this
            ->handle($request);

        $logger
            ->debug('Missing proxy callback parameter (pgtIou).')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_test_the_logger_when_no_parameter_is_in_the_url(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from/it_can_test_the_logger_when_no_parameter_is_in_the_url'),
                []
            )
        );

        $this
            ->handle($request);

        $logger
            ->debug('CAS server just checked the proxy callback endpoint.')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_test_the_logger_when_parameters_are_in_the_url(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

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

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from/it_can_test_the_logger_when_parameters_are_in_the_url'),
                [
                    'pgtId' => 'pgtId',
                    'pgtIou' => 'pgtIou',
                ]
            )
        );

        $this
            ->handle($request);

        $logger
            ->debug('Storing proxy callback parameters (pgtId and pgtIou).')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProxyCallback::class);
    }

    public function let(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

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

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);
    }
}
