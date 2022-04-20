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
use EcPhp\CasLib\Response\Factory\AuthenticationFailureFactory;
use EcPhp\CasLib\Response\Factory\ProxyFactory;
use EcPhp\CasLib\Response\Factory\ProxyFailureFactory;
use EcPhp\CasLib\Response\Factory\ServiceValidateFactory;
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
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $cache = new ArrayAdapter();

        $cacheItem = $cache->getItem('pgtIou');
        $cacheItem->set('pgtId');

        $cache
            ->save($cacheItem);

        $this->beConstructedWith(new Cas(
            CasSpecUtils::getTestPropertiesWithPgtUrl(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
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
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(new Cas(
            CasSpecUtils::getTestProperties(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            new ArrayAdapter(),
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
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
