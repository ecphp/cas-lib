<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Service;

use EcPhp\CasLib\Introspection\Introspector;
use EcPhp\CasLib\Service\Proxy;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;

class ProxySpec extends ObjectBehavior
{
    public function it_can_detect_a_wrong_proxy_response()
    {
        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationSuccess>
              <cas:user>username</cas:user>
              <cas:proxyGrantingTicket>pgtIou</cas:proxyGrantingTicket>
              <cas:proxies>
                <cas:proxy>http://app/proxyCallback.php</cas:proxy>
              </cas:proxies>
             </cas:authenticationSuccess>
            </cas:serviceResponse>
            EOF;

        $response = new Response(200, ['Content-Type' => 'application/xml'], $body);

        $this
            ->shouldThrow(Exception::class)
            ->during('getCredentials', [$response]);
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

    public function let(ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

        $this->beConstructedWith([], Cas::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());
    }
}
