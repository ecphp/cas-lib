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
use EcPhp\CasLib\Introspection\Introspector;
use EcPhp\CasLib\Utils\Uri as UtilsUri;
use Exception;
use InvalidArgumentException;
use loophp\psr17\Psr17;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Uri;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
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

    public function it_can_authenticate()
    {
        $request = new Request(
            'GET',
            'http://from/?ticket=ST-TICKET-INVALID'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request(
            'GET',
            'http://from/?ticket=ST-TICKET-VALID'
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();
    }

    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Client\ClientInterface $client
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Cache\CacheItemPoolInterface $cache
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function it_can_authenticate_a_request(ClientInterface $client, CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17, $cache, new Introspector());

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-TICKET-VALID']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-TICKET-INVALID']));

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'PT-TICKET-VALID']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', new Uri('http://from'));

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request('GET', new Uri('http://from'));

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-ticket-pgt']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-ticket-pgt-pgtiou-not-found']));

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        // This test returns a valid response because pgtUrl is not enabled.
        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-ticket-pgt-pgtiou-pgtid-null']));

        $this
            ->authenticate($request)
            ->shouldBeArray();
    }

    public function it_can_authenticate_a_request_in_proxy_mode(ClientInterface $client, CacheItemPoolInterface $cache)
    {
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

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
            ->willReturn(false);

        $this->beConstructedWith(CasSpecUtils::getTestPropertiesWithPgtUrl(), $client, $psr17, $cache, new Introspector());

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-TICKET-VALID']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-INVALID']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'PT-TICKET-VALID']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', new Uri('http://from'));

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-ticket-pgt']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-ticket-pgt-pgtiou-not-found']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-ticket-pgt-pgtiou-pgtid-null']
            )
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('authenticate', [$request]);
    }

    public function it_can_be_constructed_without_base_url(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '//////',
            'protocol' => [
                'login' => [
                    'path' => '/login',
                    'allowed_parameters' => [
                        'coin',
                    ],
                ],
            ],
        ]);

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            'http://foo'
        );

        $this
            ->login($request)
            ->getHeaders()
            ->shouldReturn(['Location' => ['/login']]);
    }

    public function it_can_check_if_the_request_needs_authentication()
    {
        $request = new Request('GET', 'http://from/');

        $this
            ->supportAuthentication($request)
            ->shouldReturn(false);

        $request = new Request('GET', 'http://from/?ticket=ticket');

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

        $request = new Request(
            'GET',
            $from
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('login', [$request, $parameters]);

        $parameters = [
            'gateway' => true,
        ];

        $request = new Request(
            'GET',
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

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());

        $parameters = [
            'service' => 'service',
            'ticket' => 'ticket',
        ];

        $request = new Request('GET', 'error');

        $this
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate', [$request, $parameters]);
    }

    public function it_can_do_a_request_to_validate_a_ticket()
    {
        $request = new Request(
            'GET',
            new Uri('http://from/it_can_do_a_request_to_validate_a_ticket/no-ticket')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);

        $request = new Request(
            'GET',
            new Uri('http://from/?ticket=ST-TICKET-VALID')
        );

        $this
            ->requestTicketValidation($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new Request(
            'GET',
            new Uri('http://from/?ticket=PT-TICKET-VALID')
        );

        $this
            ->requestTicketValidation($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new Request(
            'GET',
            new Uri('http://from?ticket=EMPTY-BODY')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);
    }

    public function it_can_handle_proxy_callback_request()
    {
        $request = new Request('GET', 'http://local/proxycallback?pgtId=pgtId&pgtIou=false');

        $this
            ->shouldThrow(Exception::class)
            ->during('handleProxyCallback', [$request]);

        $request = new Request('GET', 'http://local/proxycallback?pgtId=pgtId&pgtIou=pgtIou');

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(200);

        $request = new Request('GET', 'http://local/proxycallback?pgtId=pgtId');

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(500);

        $request = new Request('GET', 'http://local/proxycallback?pgtIou=pgtIou');

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(500);

        $request = new Request('GET', 'http://local/proxycallback');

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(200);

        $request = new Request('GET', 'http://local/proxycallback?pgtId=pgtId&pgtIou=pgtIou');

        $this->cache
            ->getItem('false')
            ->willThrow(new InvalidArgumentException('foo'));

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
        $request = new Request('GET', 'http://local/', ['referer' => 'http://google.com/']);

        $this
            ->login($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->login($request)
            ->getStatusCode()
            ->shouldReturn(302);

        $request = new Request('GET', 'http://local/');

        $this
            ->login($request)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Flocal%2F']);

        $request = new Request('GET', 'http://local/');

        $parameters = [
            'foo' => 'bar',
            'service' => 'http://foo.bar/',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Ffoo.bar%2F']);

        $parameters = [
            'custom' => 'foo',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo&service=http%3A%2F%2Flocal%2F']);

        $request = new Request('GET', 'http://local/', ['referer' => 'http://referer/']);

        $parameters = [
            'foo' => 'bar',
            'service' => 'http://foo.bar/',
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Ffoo.bar%2F']);

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
        $request = new Request('GET', 'http://local/');

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
            ->shouldReturn(['http://local/cas/logout']);

        $parameters = [
            'custom' => 'bar',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar']);

        $parameters = [
            'custom' => 'bar',
            'service' => 'http://custom.local/',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Fcustom.local%2F']);

        $request = new Request('GET', 'http://local/', ['referer' => 'http://referer/']);

        $parameters = [
            'custom' => 'bar',
        ];

        $this
            ->logout($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar']);

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

    public function it_can_parse_a_bad_proxy_request_response(CacheItemPoolInterface $cache)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_parse_a_bad_proxy_request_response')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestProxyTicket', [$request]);
    }

    public function it_can_parse_a_good_proxy_request_response(CacheItemPoolInterface $cache)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_parse_a_good_proxy_request_response')
        );

        $this
            ->requestProxyTicket($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_parse_json_in_a_response(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'serviceValidate' => [
                    'path' => 'http://local/cas/serviceValidate',
                    'allowed_parameters' => [
                        'service',
                        'ticket',
                    ],
                    'default_parameters' => [
                        'format' => 'JSON',
                    ],
                ],
            ],
        ]);

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_parse_json_in_a_response')
        );

        $this
            ->requestServiceValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_renew_login()
    {
        $request = new Request('GET', 'http://local/');

        $parameters = [
            'renew' => true,
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?renew=true&service=http%3A%2F%2Flocal%2F']);

        $request = new Request('GET', 'http://local/?renew=false');

        $this
            ->shouldThrow(Exception::class)
            ->during('login', [$request, $parameters]);
    }

    public function it_can_request_a_proxy_ticket(CacheItemPoolInterface $cache)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            'http://from/it_can_request_a_proxy_ticket'
        );

        $this
            ->requestProxyTicket($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new Request(
            'GET',
            'http://from/TestClientException'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestProxyTicket', [$request]);
    }

    public function it_can_validate_a_bad_proxy_ticket(CacheItemPoolInterface $cache)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'proxyValidate' => [
                    'path' => 'http://local/cas/proxyValidate',
                    'allowed_parameters' => [
                        'service',
                        'ticket',
                        'http_code',
                        'invalid_xml',
                        'unrelated_xml',
                    ],
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
            ],
        ]);

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'POST',
            'http://from/it_can_validate_a_bad_proxy_ticket'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestProxyValidate', [$request]);
    }

    public function it_can_validate_a_bad_service_validate_request(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'POST',
            'http://from/it_can_validate_a_bad_service_validate_request'
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate', [$request]);
    }

    public function it_can_validate_a_good_proxy_ticket(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'proxyValidate' => [
                    'path' => 'http://local/cas/proxyValidate',
                    'allowed_parameters' => [
                        'service',
                        'ticket',
                        'http_code',
                        'invalid_xml',
                        'unrelated_xml',
                    ],
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
            ],
        ]);

        $cacheItem
            ->get()
            ->willReturn('pgtIou');

        $cacheItem
            ->set('pgtId')
            ->willReturn($cacheItem);

        $cacheItem
            ->expiresAfter(300)
            ->willReturn($cacheItem);

        $cache
            ->save($cacheItem)
            ->willReturn(true);

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->hasItem('pgtIouInvalid')
            ->willReturn(false);

        // See: https://github.com/phpspec/prophecy/pull/429
        $cache
            ->hasItem('false')
            ->willThrow(new InvalidArgumentException('foo'));

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_validate_a_good_proxy_ticket')
        );

        $this
            ->requestProxyValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->requestProxyValidate($request)
            ->getStatusCode()
            ->shouldReturn(200);

        $this
            ->requestProxyValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_validate_a_good_proxy_ticket/2')
        );

        $this
            ->requestProxyValidate($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_validate_a_good_service_validate_request(CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $properties = new CasProperties([
            'base_url' => '',
            'protocol' => [
                'serviceValidate' => [
                    'path' => 'http://local/cas/serviceValidate',
                    'allowed_parameters' => [
                        'service',
                        'ticket',
                        'http_code',
                        'invalid_xml',
                        'with_pgt',
                        'pgt_valid',
                        'pgt_is_not_string',
                    ],
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
            ],
        ]);

        $cacheItem
            ->set('pgtId')
            ->willReturn($cacheItem);

        $cacheItem
            ->expiresAfter(300)
            ->willReturn($cacheItem);

        $cache
            ->save($cacheItem)
            ->willReturn(true);

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->hasItem('pgtIouInvalid')
            ->willReturn(false);

        // See: https://github.com/phpspec/prophecy/pull/429
        $cache
            ->hasItem('false')
            ->willThrow(new InvalidArgumentException('foo'));

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $psr17 = new Psr17($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_validate_a_good_service_validate_request')
        );

        $this
            ->requestServiceValidate($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_validate_a_service_ticket()
    {
        $request = new Request(
            'GET',
            new Uri('http://from/it_can_validate_a_service_ticket')
        );

        $this
            ->requestProxyValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->requestProxyValidate($request)
            ->getStatusCode()
            ->shouldReturn(200);

        $this
            ->requestProxyValidate($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_validate_a_service_ticket/404')
        );

        $this
            ->shouldThrow(Exception::class)
            ->during('requestProxyValidate', [$request]);
    }

    public function it_can_validate_any_type_of_ticket()
    {
        $request = new Request('GET', 'http://from?ticket=ST-TICKET-VALID');

        $this
            ->requestTicketValidation($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new Request('GET', 'http://from?ticket=PT-TICKET-INVALID');

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);

        $request = new Request('GET', 'http://from');

        $this
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [$request]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Cas::class);
    }

    public function let(CacheItemPoolInterface $cache, CacheItemInterface $cacheItemPgtIou, CacheItemInterface $cacheItemPgtIdNull)
    {
        $this->cache = $cache;
        $this->cacheItem = $cacheItemPgtIou;

        $properties = CasSpecUtils::getTestProperties();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

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
            ->getItem('false')
            ->willThrow(Exception::class);

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

        $this->beConstructedWith($properties, $client, $psr17, $cache, new Introspector());
    }
}
