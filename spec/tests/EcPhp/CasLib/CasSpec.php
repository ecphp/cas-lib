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
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use EcPhp\CasLib\Response\CasResponseBuilder;
use Ergebnis\Http\Method;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PhpSpec\ObjectBehavior;
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

        $this->beConstructedWith(new Cas(
            $properties,
            $client,
            $psr17,
            new ArrayAdapter(),
            new CasResponseBuilder()
        ));

        $request = new ServerRequest(
            Method::GET,
            'http://from/it_can_test_the_proxy_mode_with_pgtUrl'
        );

        $this
            ->requestTicketValidation($request, ['ticket' => 'ST-TICKET-VALID'])
            ->shouldBeAnInstanceOf(ServiceValidate::class);
    }

    public function it_can_test_the_proxy_mode_without_pgtUrl()
    {
        $properties = CasSpecUtils::getTestProperties();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(new Cas(
            $properties,
            $client,
            $psr17,
            new ArrayAdapter(),
            new CasResponseBuilder()
        ));

        $request = new ServerRequest(
            Method::GET,
            'http://from/it_can_test_the_proxy_mode_without_pgtUrl'
        );

        $this
            ->requestTicketValidation($request, ['ticket' => 'ST-TICKET-VALID'])
            ->shouldBeAnInstanceOf(ServiceValidate::class);
    }
}
