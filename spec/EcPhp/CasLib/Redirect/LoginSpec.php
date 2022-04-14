<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Redirect;

use EcPhp\CasLib\Redirect\Login;
use EcPhp\CasLib\Response\CasResponseBuilderInterface;
use Exception;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use spec\EcPhp\CasLib\Cas;
use Symfony\Component\HttpClient\Psr18Client;

class LoginSpec extends ObjectBehavior
{
    public function it_can_deal_with_array_parameters(CacheItemPoolInterface $cache, CasResponseBuilderInterface $casResponseBuilder)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $parameters = [
            'custom' => range(1, 5),
        ];

        $this->beConstructedWith(
            $parameters,
            $cache,
            $casResponseBuilder,
            new Psr18Client(Cas::getHttpClientMock()),
            Cas::getTestProperties(),
            $psr17
        );

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_deal_with_array_parameters')
        );

        $this
            ->handle($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $this
            ->handle($request)
            ->getHeaderLine('Location')
            ->shouldReturn('http://local/cas/login?custom%5B0%5D=1&custom%5B1%5D=2&custom%5B2%5D=3&custom%5B3%5D=4&custom%5B4%5D=5&service=http%3A%2F%2Ffrom%2Fit_can_deal_with_array_parameters');
    }

    public function it_can_deal_with_renew_and_gateway_parameters(CacheItemPoolInterface $cache, CasResponseBuilderInterface $casResponseBuilder)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $parameters = [
            'renew' => true,
            'gateway' => true,
            'service' => 'service',
        ];

        $this->beConstructedWith(
            $parameters,
            $cache,
            $casResponseBuilder,
            new Psr18Client(Cas::getHttpClientMock()),
            Cas::getTestProperties(),
            $psr17
        );

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_deal_with_renew_and_gateway_parameters')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$request]);
    }

    public function it_can_deal_with_renew_parameter(CacheItemPoolInterface $cache, CasResponseBuilderInterface $casResponseBuilder)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $parameters = [
            'renew' => 'coin',
            'gateway' => false,
        ];

        $this->beConstructedWith(
            $parameters,
            $cache,
            $casResponseBuilder,
            new Psr18Client(Cas::getHttpClientMock()),
            Cas::getTestProperties(),
            $psr17
        );

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_deal_with_renew_parameter')
        );

        $this
            ->handle($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_get_a_response()
    {
        $request = new Request(
            'GET',
            new Uri('http://from/it_can_deal_with_renew_parameter')
        );

        $this
            ->handle($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Login::class);
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
