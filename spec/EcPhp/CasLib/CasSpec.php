<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib;

use EcPhp\CasLib\Cas;
use EcPhp\CasLib\Configuration\Properties as CasProperties;
use EcPhp\CasLib\Exception\CasException;
use EcPhp\CasLib\Exception\CasExceptionInterface;
use EcPhp\CasLib\Exception\CasHandlerException;
use EcPhp\CasLib\Response\CasResponseBuilder;
use EcPhp\CasLib\Response\Factory\AuthenticationFailureFactory;
use EcPhp\CasLib\Response\Factory\ProxyFactory;
use EcPhp\CasLib\Response\Factory\ProxyFailureFactory;
use EcPhp\CasLib\Response\Factory\ServiceValidateFactory;
use EcPhp\CasLib\Utils\Uri as UtilsUri;
use Ergebnis\Http\Method;
use Exception;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpClient\Psr18Client;

class CasSpec extends ObjectBehavior
{
    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var \Psr\Cache\CacheItemInterface
     */
    protected $cacheItem;

    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Cache\CacheItemPoolInterface $cache
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function it_can_authenticate_a_request(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            CasSpecUtils::getTestProperties(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $request = new ServerRequest(
            Method::GET,
            'http://from?ticket=ST-TICKET-INVALID'
        );

        $this
            ->shouldThrow(CasException::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            'http://from?ticket=ST-TICKET-VALID'
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-VALID']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-INVALID']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-VALID']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeEqualTo([
                'serviceResponse' => [
                    'authenticationSuccess' => [
                        'user' => 'username',
                    ],
                ],
            ]);
    }

    public function it_can_authenticate_a_request_in_proxy_mode(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $cache
            ->hasItem('unknownPgtIou')
            ->willReturn(false);

        $cache
            ->hasItem('pgtIouWithPgtIdNull')
            ->willReturn(true);

        $cache
            ->getItem('pgtIouWithPgtIdNull')
            ->willReturn(new CacheItem());

        $this->beConstructedWith(
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
        );

        $request = new ServerRequest(
            Method::GET,
            'http://from?ticket=ST-TICKET-INVALID'
        );

        $this
            ->shouldThrow(CasException::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            'http://from?ticket=ST-TICKET-VALID'
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-VALID']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-INVALID']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-VALID']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-VALID']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeEqualTo([
                'serviceResponse' => [
                    'authenticationSuccess' => [
                        'user' => 'username',
                        'proxyGrantingTicket' => 'pgtId',
                    ],
                ],
            ]);

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-ticket-pgt-pgtiou-not-found']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new ServerRequest(
            Method::GET,
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-ticket-pgt-pgtiou-pgtid-null']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);
    }

    public function it_can_check_if_the_request_needs_authentication()
    {
        $request = new ServerRequest(
            Method::GET,
            'http://from'
        );

        $this
            ->supportAuthentication($request)
            ->shouldReturn(false);

        $request = new ServerRequest(Method::GET, 'http://from?ticket=ticket');

        $this
            ->supportAuthentication($request)
            ->shouldReturn(true);
    }

    public function it_can_detect_when_gateway_and_renew_are_set_together()
    {
        $from = 'http://local/';

        $parameters = [
            'renew' => true,
            'gateway' => true,
        ];

        $request = new ServerRequest(
            Method::GET,
            $from
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('login', [$request, $parameters]);

        $parameters = [
            'gateway' => true,
        ];

        $request = new ServerRequest(
            Method::GET,
            $from . '?gateway=false'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('login', [$request, $parameters]);
    }

    public function it_can_detect_wrong_url(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'serviceValidate' => [
                    'path' => '\?&!@# // \\ http:// foo bar',
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
            ],
        ]);

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            $properties,
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $parameters = [
            'service' => 'service',
            'ticket' => 'ticket',
        ];

        $request = new ServerRequest(Method::GET, 'error');

        $this
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate', [$request, $parameters]);
    }

    public function it_can_do_a_request_to_validate_a_ticket()
    {
        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_do_a_request_to_validate_a_ticket/no-ticket')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from?ticket=ST-TICKET-VALID')
        );

        $response = $this
            ->requestTicketValidation($request);

        $response
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $response
            ->shouldThrow(Exception::class)
            ->during('getProxyGrantingTicket', [$request]);

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from?ticket=EMPTY-BODY')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);
    }

    public function it_can_handle_proxy_callback_request()
    {
        $request = new ServerRequest(
            Method::GET,
            'http://local/proxycallback?pgtId=pgtId&pgtIou=pgtIou'
        );

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(200);

        $request = new ServerRequest(
            Method::GET,
            'http://local/proxycallback?pgtId=pgtId'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('handleProxyCallback', [$request]);

        $request = new ServerRequest(
            Method::GET,
            'http://local/proxycallback?pgtIou=pgtIou'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('handleProxyCallback', [$request]);

        $request = new ServerRequest(
            Method::GET,
            'http://local/proxycallback'
        );

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(200);
    }

    public function it_can_login()
    {
        $request = new ServerRequest(
            Method::GET,
            'http://local/',
            ['referer' => 'http://google.com/']
        );

        $this
            ->login($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->login($request)
            ->getStatusCode()
            ->shouldReturn(302);

        $request = new ServerRequest(Method::GET, 'http://local/');

        $this
            ->login($request)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Flocal%2F']);

        $request = new ServerRequest(Method::GET, 'http://local/');

        $parameters = [
            'foo' => 'bar',
            'service' => 'http://foo.bar/',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?foo=bar&service=http%3A%2F%2Ffoo.bar%2F']);

        $parameters = [
            'custom' => 'foo',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo&service=http%3A%2F%2Flocal%2F']);

        $request = new ServerRequest(Method::GET, 'http://local/', ['referer' => 'http://referer/']);

        $parameters = [
            'foo' => 'bar',
            'service' => 'http://foo.bar/',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?foo=bar&service=http%3A%2F%2Ffoo.bar%2F']);

        $parameters = [
            'custom' => 'foo',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo&service=http%3A%2F%2Flocal%2F']);

        $parameters = [
            'custom' => 'foo',
            'service' => null,
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo']);
    }

    public function it_can_logout()
    {
        $request = new ServerRequest(Method::GET, 'http://local/');

        $this
            ->logout($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->logout($request)
            ->getStatusCode()
            ->shouldReturn(302);

        $this
            ->logout($request)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?service=http%3A%2F%2Flocal%2F']);

        $parameters = [
            'custom' => 'bar',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Flocal%2F']);

        $parameters = [
            'custom' => 'bar',
            'service' => 'http://custom.local/',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Fcustom.local%2F']);

        $request = new ServerRequest(Method::GET, 'http://local/', ['referer' => 'http://referer/']);

        $parameters = [
            'custom' => 'bar',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Flocal%2F']);

        $parameters = [
            'custom' => 'bar',
            'service' => 'http://custom.local/',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Fcustom.local%2F']);

        $parameters = [
            'service' => 'service',
        ];

        $this
            ->logout($request, $parameters)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_parse_json_in_a_response(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'serviceValidate' => [
                    'path' => 'http://local/cas/serviceValidate',
                    'default_parameters' => [
                        'format' => 'JSON',
                    ],
                ],
            ],
        ]);

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            $properties,
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_parse_json_in_a_response')
        );

        $this
            ->requestServiceValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_renew_login()
    {
        $request = new ServerRequest(Method::GET, 'http://local/');

        $parameters = [
            'renew' => true,
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?renew=true&service=http%3A%2F%2Flocal%2F']);

        $request = new ServerRequest(Method::GET, 'http://local/?renew=false');

        $this
            ->shouldThrow(Exception::class)
            ->during('login', [$request, $parameters]);
    }

    public function it_can_request_a_proxy_ticket(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            CasSpecUtils::getTestProperties(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_request_a_proxy_ticket')
        );

        $this
            ->requestProxyTicket($request, ['service' => 'service-valid'])
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_request_a_proxy_ticket')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestProxyTicket', [$request, ['service' => 'service-invalid']]);
    }

    public function it_can_validate_a_bad_proxy_ticket(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'proxyValidate' => [
                    'path' => 'http://local/cas/proxyValidate',
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
            ],
        ]);

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        $this->beConstructedWith(
            $properties,
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $request = new ServerRequest(
            Method::POST,
            'http://from/it_can_validate_a_bad_proxy_ticket'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate', [$request]);
    }

    public function it_can_validate_a_bad_service_validate_request(CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            CasSpecUtils::getTestProperties(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $request = new ServerRequest(
            Method::GET,
            'http://from/it_can_validate_a_bad_service_validate_request'
        );

        $this
            ->shouldThrow(CasHandlerException::class)
            ->during(
                'requestServiceValidate',
                [
                    $request,
                    ['ticket' => 'ST-TICKET-INVALID'],
                ]
            );
    }

    public function it_can_validate_a_service_ticket()
    {
        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_validate_a_service_ticket')
        );

        $this
            ->requestServiceValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->requestServiceValidate($request)
            ->getStatusCode()
            ->shouldReturn(200);

        $this
            ->requestServiceValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $request = new ServerRequest(
            Method::GET,
            new Uri('http://from/it_can_validate_a_service_ticket/404')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate', [$request]);
    }

    public function it_can_validate_any_type_of_ticket()
    {
        $request = new ServerRequest(Method::GET, 'http://from?ticket=ST-TICKET-VALID');

        $this
            ->requestTicketValidation($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new ServerRequest(Method::GET, 'http://from?ticket=ST-TICKET-INVALID');

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);

        $request = new ServerRequest(Method::GET, 'http://from/it_can_validate_any_type_of_ticket/ticket-is-available-but-invalid');

        $this
            ->shouldThrow(CasExceptionInterface::class)
            ->during('requestTicketValidation', [$request, ['ticket' => 'ticket-invalid']]);

        $request = new ServerRequest(Method::GET, 'http://from/it_can_validate_any_type_of_ticket/ticket-is-unavailable');

        $this
            ->shouldThrow(CasExceptionInterface::class)
            ->during('requestTicketValidation', [$request]);
    }

    public function it_cannot_be_constructed_without_base_url(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '//////',
            'protocol' => [
                'login' => [
                    'path' => '/login',
                ],
            ],
        ]);

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith(
            $properties,
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );

        $request = new ServerRequest(
            Method::GET,
            'http://foo'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('login', [$request]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Cas::class);
    }

    public function let(CacheItemPoolInterface $cache, CacheItemInterface $cacheItemPgtIou, CacheItemInterface $cacheItemPgtIdNull)
    {
        $this->cache = $cache;
        $this->cacheItem = $cacheItemPgtIou;

        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $cacheItemPgtIou
            ->set('pgtId')
            ->willReturn($cacheItemPgtIou);

        $cacheItemPgtIou
            ->expiresAfter(300)
            ->willReturn($cacheItemPgtIou);

        $cacheItemPgtIou
            ->get()
            ->willReturn('pgtIou');

        $cache
            ->hasItem('unknownPgtIou')
            ->willReturn(false);

        $cache
            ->save($cacheItemPgtIou)
            ->willReturn(true);

        $cache
            ->hasItem('pgtIou')
            ->willReturn(false);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItemPgtIou);

        $cache
            ->hasItem('pgtIouWithPgtIdNull')
            ->willReturn(true);

        $cacheItemPgtIdNull
            ->set(null)
            ->willReturn($cacheItemPgtIdNull);

        $cacheItemPgtIdNull
            ->expiresAfter(300)
            ->willReturn($cacheItemPgtIdNull);

        $cacheItemPgtIdNull
            ->get()
            ->willReturn(null);

        $cache
            ->getItem('pgtIouWithPgtIdNull')
            ->willReturn($cacheItemPgtIdNull);

        $this->beConstructedWith(
            CasSpecUtils::getTestProperties(),
            new Psr18Client(CasSpecUtils::getHttpClientMock()),
            $psr17,
            $cache,
            new CasResponseBuilder(
                new AuthenticationFailureFactory(),
                new ProxyFactory(),
                new ProxyFailureFactory(),
                new ServiceValidateFactory()
            )
        );
    }
}
