<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib;

use EcPhp\CasLib\Cas;
use EcPhp\CasLib\Configuration\Properties as CasProperties;
use Exception;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use Nyholm\Psr7Server\ServerRequestCreator;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpClient\Psr18Client;
use TypeError;

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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function it_can_authenticate()
    {
        $uri = 'http://from?ticket=Not-Authenticated';

        $this
            ->withServerRequest(new ServerRequest('GET', 'http://from?ticket=Not-Authenticated'))
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $request = new ServerRequest('GET', 'http://from?ticket=ST-ticket');

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $request = new ServerRequest('GET', 'http://from?ticket=FOO-TICKET');

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');
    }

    public function it_can_authenticate_a_request_in_service_mode(ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $uri = new Uri('http://from?ticket=ST-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket']);

        $this->beConstructedWith($request, CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from?ticket=foo');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'foo']);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $uri = new Uri('http://from?ticket=PT-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'PT-ticket']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from');
        $request = new ServerRequest('GET', $uri);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $uri = new Uri('http://from?ticket=ST-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from?ticket=ST-FOO');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-FOO']);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');
    }

    public function it_can_authenticate_a_request_in_proxy_mode(ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $uri = new Uri('http://from-proxy-validate?ticket=ST-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket']);

        $this->beConstructedWith($request, CasSpecUtils::getTestPropertiesWithPgtUrl(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from?ticket=foo');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'foo']);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $uri = new Uri('http://from?ticket=PT-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'PT-ticket']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from');
        $request = new ServerRequest('GET', $uri);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $uri = new Uri('http://from?ticket=ST-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from?ticket=ST-FOO');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-FOO']);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $uri = new Uri('http://from?ticket=ST-ticket-pgt');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket-pgt']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from?ticket=ST-ticket-pgt-pgtiou-not-found');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket-pgt-pgtiou-not-found']);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        $uri = new Uri('http://from?ticket=ST-ticket-pgt');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket-pgt']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeArray();

        $uri = new Uri('http://from?ticket=ST-ticket-pgt-pgtiou-not-found');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket-pgt-pgtiou-not-found']);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('authenticate');

        // TODO: Make this example working with ProxyValidation
        /*
        $uri = new Uri('http://from?ticket=ST-ticket-pgt-pgtiou-pgtid-null');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket-pgt-pgtiou-pgtid-null']);

        $this
            ->withServerRequest($request)
            ->authenticate()
            ->shouldBeNull();
         */
    }

    public function it_can_be_constructed_without_base_url(LoggerInterface $logger, CacheItemPoolInterface $cache)
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
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();
        $this->beConstructedWith($serverRequest, $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new ServerRequest('GET', 'http://foo');

        $this
            ->withServerRequest($request)
            ->login()
            ->getHeaders()
            ->shouldReturn(['Location' => ['/login']]);
    }

    public function it_can_check_if_the_logger_works_during_a_failed_proxy_validate_request(ServerRequestInterface $serverRequest, ClientInterface $client, UriFactoryInterface $uriFactory, RequestFactoryInterface $requestFactory, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $uri = new Uri('http://from?ticket=BAD-http-query');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'BAD-ticket']);

        $this->beConstructedWith($request, CasSpecUtils::getTestPropertiesWithPgtUrl(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->shouldThrow(Exception::class)
            ->during('requestProxyValidate');
    }

    public function it_can_check_if_the_logger_works_during_a_failed_service_validate_request(ServerRequestInterface $serverRequest, ClientInterface $client, UriFactoryInterface $uriFactory, RequestFactoryInterface $requestFactory, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $uri = new Uri('http://from?ticket=BAD-http-query');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'BAD-ticket']);

        $this->beConstructedWith($request, CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate');
    }

    public function it_can_check_if_the_logger_works_during_a_successful_authentication_of_service_ticket(ServerRequestInterface $serverRequest, ClientInterface $client, UriFactoryInterface $uriFactory, RequestFactoryInterface $requestFactory, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $cacheItem = new CacheItem();
        $cacheItem->set('pgtId');

        $cache
            ->hasItem('pgtIou')
            ->willReturn(true);

        $cache
            ->getItem('pgtIou')
            ->willReturn($cacheItem);

        $uri = new Uri('http://from?ticket=ST-ticket');
        $request = (new ServerRequest('GET', $uri))
            ->withQueryParams(['ticket' => 'ST-ticket']);

        $this->beConstructedWith($request, CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $response = $this
            ->getWrappedObject()
            ->authenticate();
    }

    public function it_can_check_if_the_request_needs_authentication()
    {
        $from = 'http://local/page';
        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->supportAuthentication()
            ->shouldReturn(false);

        $from = 'http://local/page?ticket=ticket';
        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->supportAuthentication()
            ->shouldReturn(true);
    }

    public function it_can_detect_when_gateway_and_renew_are_set_together()
    {
        $from = 'http://local/';

        $parameters = [
            'renew' => true,
            'gateway' => true,
        ];

        $this
            ->withServerRequest(new ServerRequest('GET', $from))
            ->shouldThrow(Exception::class)
            ->during('login', [$parameters]);

        $parameters = [
            'gateway' => true,
        ];

        $this
            ->withServerRequest(new ServerRequest('GET', $from . '?gateway=false'))
            ->shouldThrow(Exception::class)
            ->during('login', [$parameters]);
    }

    public function it_can_detect_wrong_url(LoggerInterface $logger, CacheItemPoolInterface $cache)
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
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

        $this->beConstructedWith($serverRequest, $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $parameters = [
            'service' => 'service',
            'ticket' => 'ticket',
        ];

        $this
            ->withServerRequest(new ServerRequest('GET', 'error'))
            ->shouldThrow(TypeError::class)
            ->during('requestServiceValidate', $parameters);
    }

    public function it_can_do_a_request_to_validate_a_ticket()
    {
        $from = 'http://local/cas/serviceValidate?service=service';
        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation');

        $from = 'http://local/cas/serviceValidate?service=service&ticket=ST-ticket';
        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->requestTicketValidation()
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $from = 'http://local/cas/proxyValidate?service=service&ticket=PT-ticket';
        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->requestTicketValidation()
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        // Empty body - not existing request
        $from = 'http://local/cas/proxyValidate?service=service&ticket=bar';
        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation');

        $from = 'http://local/cas/proxyValidate?service=service';
        $this
            ->withServerRequest(new ServerRequest('GET', $from))
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation');

        // Empty body - not existing request
        $from = 'http://local/cas/serviceValidate?service=service&ticket=ST-ticket';
        $this
            ->withServerRequest(new ServerRequest('GET', $from))
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [['service' => 'foo', 'ticket' => 'bar']]);
    }

    public function it_can_handle_proxy_callback_request(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $request = new ServerRequest('GET', 'http://local/proxycallback?pgtId=pgtId&pgtIou=false');

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->getStatusCode()
            ->shouldReturn(500);

        $request = new ServerRequest('GET', 'http://local/proxycallback?pgtIou=pgtIou&pgtId=pgtId');

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->getStatusCode()
            ->shouldReturn(200);

        $request = new ServerRequest('GET', 'http://local/proxycallback?pgtId=pgtId');

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->getStatusCode()
            ->shouldReturn(500);

        $request = new ServerRequest('GET', 'http://local/proxycallback?pgtIou=pgtIou');

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->getStatusCode()
            ->shouldReturn(500);

        $request = new ServerRequest('GET', 'http://local/proxycallback');

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->getStatusCode()
            ->shouldReturn(200);

        $request = new ServerRequest('GET', 'http://local/proxycallback?pgtId=pgtId&pgtIou=pgtIou');

        $this->cache
            ->getItem('false')
            ->willThrow(new InvalidArgumentException('foo'));

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback()
            ->getStatusCode()
            ->shouldReturn(200);

        $response = new Response(200);

        $this
            ->withServerRequest($request)
            ->handleProxyCallback([])
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_login()
    {
        $request = new ServerRequest('GET', 'http://local/', ['referer' => 'http://google.com/']);

        $this
            ->withServerRequest($request)
            ->login()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->login()
            ->getStatusCode()
            ->shouldReturn(302);

        $request = new ServerRequest('GET', 'http://local/');

        $this
            ->withServerRequest($request)
            ->login()
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Flocal%2F']);

        $request = new ServerRequest('GET', 'http://local/');

        $parameters = [
            'foo' => 'bar',
            'service' => 'http://foo.bar/',
        ];

        $this
            ->withServerRequest($request)
            ->login($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Ffoo.bar%2F']);

        $parameters = [
            'custom' => 'foo',
        ];

        $this
            ->withServerRequest($request)
            ->login($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo&service=http%3A%2F%2Flocal%2F']);

        $request = new ServerRequest('GET', 'http://local/', ['referer' => 'http://referer/']);

        $parameters = [
            'foo' => 'bar',
            'service' => 'http://foo.bar/',
        ];

        $this
            ->withServerRequest($request)
            ->login($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?service=http%3A%2F%2Ffoo.bar%2F']);

        $parameters = [
            'custom' => 'foo',
        ];

        $this
            ->withServerRequest($request)
            ->login($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo&service=http%3A%2F%2Flocal%2F']);

        $parameters = [
            'custom' => 'foo',
            'service' => null,
        ];

        $this
            ->withServerRequest($request)
            ->login($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?custom=foo']);
    }

    public function it_can_logout()
    {
        $request = new ServerRequest('GET', 'http://local/');

        $this
            ->withServerRequest($request)
            ->logout()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->logout()
            ->getStatusCode()
            ->shouldReturn(302);

        $this
            ->withServerRequest($request)
            ->logout()
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout']);

        $parameters = [
            'custom' => 'bar',
        ];

        $this
            ->withServerRequest($request)
            ->logout($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar']);

        $parameters = [
            'custom' => 'bar',
            'service' => 'http://custom.local/',
        ];

        $this
            ->withServerRequest($request)
            ->logout($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Fcustom.local%2F']);

        $request = new ServerRequest('GET', 'http://local/', ['referer' => 'http://referer/']);

        $parameters = [
            'custom' => 'bar',
        ];

        $this
            ->withServerRequest($request)
            ->logout($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar']);

        $parameters = [
            'custom' => 'bar',
            'service' => 'http://custom.local/',
        ];

        $this
            ->withServerRequest($request)
            ->logout($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/logout?custom=bar&service=http%3A%2F%2Fcustom.local%2F']);

        $parameters = [
            'service' => 'service',
        ];

        $this
            ->withServerRequest($request)
            ->logout($parameters)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_parse_a_bad_proxy_request_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();
        $this->beConstructedWith($serverRequest, CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $url = 'http://from';

        $this
            ->withServerRequest(new ServerRequest('GET', $url))
            ->shouldThrow(Exception::class)
            ->during('requestProxyTicket', [['targetService' => 'targetService', 'pgt' => 'pgt-error-in-getCredentials']]);
    }

    public function it_can_parse_a_good_proxy_request_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();
        $this->beConstructedWith($serverRequest, CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $url = 'http://from';

        $this
            ->withServerRequest(new ServerRequest('GET', $url))
            ->requestProxyTicket(['targetService' => 'targetService', 'pgt' => 'pgt'])
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldNotBeCalled();
    }

    public function it_can_parse_json_in_a_response(LoggerInterface $logger, CacheItemPoolInterface $cache)
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
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();
        $this->beConstructedWith($serverRequest, $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new ServerRequest('GET', 'http://local/cas/serviceValidate?service=service&ticket=ticket&format=JSON');

        $this
            ->withServerRequest($request)
            ->requestServiceValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_renew_login()
    {
        $from = 'http://local/';

        $request = new ServerRequest('GET', $from);

        $parameters = [
            'renew' => true,
        ];

        $this
            ->withServerRequest($request)
            ->login($parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?renew=true&service=http%3A%2F%2Flocal%2F%3Frenew%3D0']);

        $request = new ServerRequest('GET', $from . '?renew=false');

        $parameters = [
            'renew' => true,
        ];

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('login', [$parameters]);
    }

    public function it_can_request_a_proxy_ticket(LoggerInterface $logger, CacheItemPoolInterface $cache)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

        //$logger = new Logger('psrcas', [new StreamHandler('php://stderr')]);

        $this->beConstructedWith($serverRequest, CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $url = 'http://from';

        $this
            ->withServerRequest(new ServerRequest('GET', $url))
            ->requestProxyTicket(['targetService' => 'targetService', 'pgt' => 'pgt'])
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $url = 'http://from?error=TestClientException';

        $this
            ->withServerRequest(new ServerRequest('GET', $url))
            ->shouldThrow(Exception::class)
            ->during('requestProxyTicket', [['targetService' => 'targetService', 'pgt' => 'pgt']]);
    }

    public function it_can_validate_a_good_proxy_ticket(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
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
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

        //$logger = new Logger('psrcas', [new StreamHandler('php://stderr')]);

        $this->beConstructedWith($serverRequest, $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new ServerRequest('GET', 'http://local/cas/proxyValidate?service=service&ticket=ticket');

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->getStatusCode()
            ->shouldReturn(200);

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $request = new ServerRequest('GET', 'http://local/cas/proxyValidate?service=service&ticket=ticket&renew=true');

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $url = 'http://local/cas/proxyValidate?ticket=PT-ticket-pgt&service=http%3A%2F%2Ffrom';

        $this
            ->withServerRequest(new ServerRequest('GET', $url))
            ->requestProxyValidate()
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldNotHaveBeenCalled();
    }

    public function it_can_validate_a_good_service_validate_request(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
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
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $this->beConstructedWith($creator->fromGlobals(), $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $from = 'http://local/';

        $request = new ServerRequest('GET', $from);

        $parameters = [
            'service' => 'service',
            'ticket' => 'ticket',
        ];

        $this
            ->withServerRequest($request)
            ->requestServiceValidate($parameters)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldNotHaveBeenCalled();
    }

    public function it_can_validate_a_service_ticket()
    {
        $request = new ServerRequest('GET', 'http://local/cas/serviceValidate?service=service&ticket=ticket');

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->getStatusCode()
            ->shouldReturn(200);

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $request = new ServerRequest('GET', 'http://local/cas/serviceValidate?service=service&ticket=ticket&http_code=404');

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestProxyValidate');

        $request = new ServerRequest('GET', 'http://local/cas/serviceValidate?service=service&ticket=ticket&renew=true');

        $this
            ->withServerRequest($request)
            ->requestProxyValidate()
            ->shouldReturnAnInstanceOf(ResponseInterface::class);
    }

    public function it_can_validate_any_type_of_ticket()
    {
        $body = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'user' => 'username',
                ],
            ],
        ];

        $request = new ServerRequest('GET', 'http://from?ticket=ST-TICKET');
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $this
            ->withServerRequest($request)
            ->requestTicketValidation([], $response)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $body = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'user' => 'username',
                    'proxyGrantingTicket' => 'pgtIou',
                ],
            ],
        ];

        $request = new ServerRequest('GET', 'http://from?ticket=PT-TICKET');
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [[], $response]);

        $body = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'user' => 'username',
                ],
            ],
        ];

        $request = new ServerRequest('GET', 'http://from');
        $response = new Response(
            500,
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestTicketValidation', [[], $response]);
    }

    public function it_cannot_validate_a_bad_proxy_ticket(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
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
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

        $this->beConstructedWith($serverRequest, $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $request = new ServerRequest('POST', 'foo');

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestProxyValidate');

        $url = 'http://local/cas/proxyValidate?service=service&ticket=ticket&error=TestClientException';

        $this
            ->withServerRequest(new ServerRequest('GET', $url))
            ->shouldThrow(Exception::class)
            ->during('requestProxyValidate');
    }

    public function it_cannot_validate_a_bad_service_validate_request(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $this->beConstructedWith($creator->fromGlobals(), CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $from = 'http://from/';

        $parameters = [
            'service' => 'service',
            'ticket' => 'ticket-failure',
        ];

        $request = new ServerRequest('GET', $from);

        $this
            ->withServerRequest($request)
            ->shouldThrow(Exception::class)
            ->during('requestServiceValidate', [$parameters]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Cas::class);
    }

    public function let(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItemPgtIou, CacheItemInterface $cacheItemPgtIdNull)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->cacheItem = $cacheItemPgtIou;

        $properties = CasSpecUtils::getTestProperties();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

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

        $this->beConstructedWith($serverRequest, $properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);
    }
}
