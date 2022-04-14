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
use EcPhp\CasLib\Response\CasResponseBuilderInterface;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use spec\EcPhp\CasLib\Cas;
use Symfony\Component\HttpClient\Psr18Client;

class LogoutSpec extends ObjectBehavior
{
    public function it_can_get_a_response()
    {
        $request = new ServerRequest(
            'GET',
            'http://from/it_can_get_a_response'
        );

        $this
            ->handle($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Logout::class);
    }

    public function let(CacheItemPoolInterface $cache, CasResponseBuilderInterface $casResponseBuilder)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            [],
            $cache,
            $casResponseBuilder,
            new Psr18Client(Cas::getHttpClientMock()),
            Cas::getTestProperties(),
            $psr17
        );
    }
}
