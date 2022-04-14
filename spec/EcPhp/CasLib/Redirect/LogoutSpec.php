<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Redirect;

use EcPhp\CasLib\Redirect\Logout;
use loophp\psr17\Psr17;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;

class LogoutSpec extends ObjectBehavior
{
    public function it_can_get_a_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith([], Cas::getTestProperties(), $psr17, $cache, $logger);

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_get_a_response')
        );

        $this
            ->handle($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_is_initializable(CacheItemPoolInterface $cache, LoggerInterface $logger, Psr17Interface $psr17)
    {
        $this->beConstructedWith([], Cas::getTestProperties(), $psr17, $cache, $logger);

        $this->shouldHaveType(Logout::class);
    }
}
