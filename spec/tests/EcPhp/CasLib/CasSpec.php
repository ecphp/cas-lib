<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\tests\EcPhp\CasLib;

use EcPhp\CasLib\Cas;
use EcPhp\CasLib\Introspection\Introspector;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Psr18Client;

class CasSpec extends ObjectBehavior
{
    public function it_can_test_the_proxy_mode_with_pgtUrl()
    {
        $properties = CasSpecUtils::getTestPropertiesWithPgtUrl();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $introspector = new Introspector();

        $this->beConstructedWith(new Cas(
            $properties,
            $client,
            $psr17,
            new ArrayAdapter(),
            new NullLogger(),
            $introspector
        ));

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_test_the_proxy_mode_with_pgtUrl')
        );

        $this
            ->requestTicketValidation($request, ['ticket' => 'ST-TICKET-VALID'])
            ->getBody()
            ->__toString()
            ->shouldReturn('{"serviceResponse":{"authenticationSuccess":{"user":"username","proxies":{"proxy":"http:\/\/app\/proxyCallback.php"}}}}');
    }

    public function it_can_test_the_proxy_mode_without_pgtUrl()
    {
        $properties = CasSpecUtils::getTestProperties();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $introspector = new Introspector();

        $this->beConstructedWith(new Cas(
            $properties,
            $client,
            $psr17,
            new ArrayAdapter(),
            new NullLogger(),
            $introspector
        ));

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_test_the_proxy_mode_without_pgtUrl')
        );

        $this
            ->requestTicketValidation($request, ['ticket' => 'ST-TICKET-VALID'])
            ->getBody()
            ->__toString()
            ->shouldReturn('{"serviceResponse":{"authenticationSuccess":{"user":"username"}}}');
    }
}
