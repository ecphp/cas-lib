<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Service;

use EcPhp\CasLib\Service\Proxy;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;
use Symfony\Component\HttpClient\Psr18Client;

class ProxySpec extends ObjectBehavior
{
    public function it_can_detect_a_wrong_proxy_response()
    {
        $this
            ->handle(new ServerRequest('GET', 'http://from'))
            ->shouldThrow(Exception::class)
            ->during('getCredentials');
    }

    public function it_can_detect_when_no_credentials()
    {
        $response = new Response(500);

        $this
            ->shouldThrow(Exception::class)
            ->during('getCredentials', [$response]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Proxy::class);
    }

    public function let(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $client = new Psr18Client(Cas::getHttpClientMock());

        $this->beConstructedWith([], Cas::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);
    }
}
