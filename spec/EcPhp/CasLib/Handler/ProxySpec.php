<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Handler;

use EcPhp\CasLib\Handler\Proxy;
use EcPhp\CasLib\Response\CasResponseBuilder;
use EcPhp\CasLib\Response\Factory\AuthenticationFailureFactory;
use EcPhp\CasLib\Response\Factory\ProxyFactory;
use EcPhp\CasLib\Response\Factory\ProxyFailureFactory;
use EcPhp\CasLib\Response\Factory\ServiceValidateFactory;
use Ergebnis\Http\Method;
use Exception;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\ServerRequest;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use spec\EcPhp\CasLib\Cas;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\HttpClient\Psr18Client;

class ProxySpec extends ObjectBehavior
{
    public function it_can_detect_a_wrong_proxy_response()
    {
        $request = new ServerRequest(
            Method::GET,
            'http://from/it_can_detect_a_wrong_proxy_response'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$request]);
    }

    public function it_can_detect_when_no_credentials()
    {
        $request = new ServerRequest(Method::GET, 'http://from');

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$request]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Proxy::class);
    }

    public function let(CacheItemPoolInterface $cache, Psr17Interface $psr17)
    {
        $this->beConstructedWith(
            [],
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            ),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            Cas::getTestProperties(),
            $psr17
        );
    }
}
