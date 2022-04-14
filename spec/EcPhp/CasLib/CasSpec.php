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
use EcPhp\CasLib\Introspection\ServiceValidate;
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
        $request = new Request(
            'GET',
            'http://from?ticket=ST-TICKET-INVALID'
        );

        $this
            ->authenticate($request)
            ->shouldBeNull();

        $request = new Request(
            'GET',
            'http://from?ticket=ST-TICKET-VALID'
        );

        $this
            ->authenticate($request)
            ->shouldBeArray();
    }

    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Client\ClientInterface $client
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Message\UriFactoryInterface $uriFactory
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Message\RequestFactoryInterface $requestFactory
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Http\Message\StreamFactoryInterface $streamFactory
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Cache\CacheItemPoolInterface $cache
     * @param \PhpSpec\Wrapper\Collaborator|\Psr\Log\LoggerInterface $logger
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function it_can_authenticate_a_request(ServerRequestInterface $serverRequest, ClientInterface $client, UriFactoryInterface $uriFactory, RequestFactoryInterface $requestFactory, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, CacheItemPoolInterface $cache, LoggerInterface $logger)
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

        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-TICKET-VALID']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-TICKET-INVALID']));

        $this
            ->authenticate($request)
            ->shouldBeNull();

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'PT-TICKET-VALID']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', new Uri('http://from'));

        $this
            ->authenticate($request)
            ->shouldBeNull();

        $request = new Request('GET', new Uri('http://from'));

        $this
            ->authenticate($request)
            ->shouldBeNull();

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-ticket-pgt']));

        $this
            ->authenticate($request)
            ->shouldBeArray();

        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-ticket-pgt-pgtiou-not-found']));

        $this
            ->authenticate($request)
            ->shouldBeNull();

        // This test returns a valid response because pgtUrl is not enabled.
        $request = new Request('GET', UtilsUri::withParams(new Uri('http://from'), ['ticket' => 'ST-ticket-pgt-pgtiou-pgtid-null']));

        $this
            ->authenticate($request)
            ->shouldBeArray();
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

        $cache
            ->hasItem('unknownPgtIou')
            ->willReturn(false);

        $cache
            ->hasItem('pgtIouWithPgtIdNull')
            ->willReturn(false);

        $this->beConstructedWith(CasSpecUtils::getTestPropertiesWithPgtUrl(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

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
            ->authenticate($request)
            ->shouldBeNull();

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
            ->authenticate($request)
            ->shouldBeNull();

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
            ->authenticate($request)
            ->shouldBeNull();

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-ticket-pgt-pgtiou-pgtid-null']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeNull();
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

        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            'http://foo'
        );

        $this
            ->login($request)
            ->getHeaders()
            ->shouldReturn(['Location' => ['/login']]);
    }

    public function it_can_check_if_the_logger_works_during_a_failed_authentication_of_service_ticket(ClientInterface $client, CacheItemPoolInterface $cache, LoggerInterface $logger)
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

        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'EMPTY-BODY']
            )
        );

        $this
            ->authenticate($request)
            ->shouldBeNull();

        $logger
            ->error('Unable to parse the response with the specified format {format}.', ['format' => 'XML', 'response' => ''])
            ->shouldHaveBeenCalledOnce();

        $logger
            ->error('Unable to parse the response during the normalization process.', ['body' => ''])
            ->shouldHaveBeenCalledOnce();

        $logger
            ->error('Unable to detect the response format.')
            ->shouldHaveBeenCalledOnce();

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldHaveBeenCalledOnce();

        $logger
            ->error('Unable to authenticate the request.')
            ->shouldHaveBeenCalledOnce();
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

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'BAD-http-query']
            )
        );

        $this->beConstructedWith(CasSpecUtils::getTestPropertiesWithPgtUrl(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $this
            ->requestProxyValidate($request)
            ->shouldBeNull();

        $logger
            ->error('Error during the proxy validate request.')
            ->shouldHaveBeenCalledOnce();
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

        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'BAD-http-query']
            )
        );

        $this
            ->requestServiceValidate($request)
            ->shouldBeNull();

        $logger
            ->error('Error during the service validate request.')
            ->shouldHaveBeenCalledOnce();
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

        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            UtilsUri::withParams(
                new Uri('http://from'),
                ['ticket' => 'ST-TICKET-VALID']
            )
        );

        $response = $this
            ->getWrappedObject()
            ->authenticate($request);

        $logger
            ->debug('Response normalization succeeded.', ['body' => json_encode($response)])
            ->shouldHaveBeenCalledOnce();
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

    public function it_can_detect_the_type_of_a_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationSuccess>
              <cas:user>username</cas:user>
             </cas:authenticationSuccess>
            </cas:serviceResponse>
            EOF;

        $headers = [
            'Content-Type' => 'application/xml',
        ];

        $response = new Response(
            200,
            $headers,
            $body
        );

        $this
            ->detect(
                $response
            )
            ->shouldReturnAnInstanceOf(ServiceValidate::class);

        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationSuccess>
              <cas:user>username</cas:user>
             </cas:authenticationSuccess>
            </cas:serviceResponse>
            EOF;

        $headers = [
            'Content-Type' => 'application/foo',
        ];

        $response = new Response(
            200,
            $headers,
            $body
        );

        $this
            ->shouldThrow(InvalidArgumentException::class)
            ->during('detect', [$response]);
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
            ->login($request, $parameters)
            ->shouldBeNull();

        $parameters = [
            'gateway' => true,
        ];

        $request = new Request(
            'GET',
            $from . '?gateway=false'
        );

        $this
            ->login($request, $parameters)
            ->shouldBeNull();
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

        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $parameters = [
            'service' => 'service',
            'ticket' => 'ticket',
        ];

        $request = new Request('GET', 'error');

        $this
            ->requestServiceValidate($request, $parameters)
            ->shouldBeNull();
    }

    public function it_can_do_a_request_to_validate_a_ticket()
    {
        $request = new Request(
            'GET',
            new Uri('http://from/it_can_do_a_request_to_validate_a_ticket/no-ticket')
        );

        $this
            ->requestTicketValidation($request)
            ->shouldBeNull();

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
            ->requestTicketValidation($request)
            ->shouldBeNull();
    }

    public function it_can_handle_proxy_callback_request(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $request = new Request('GET', 'http://local/proxycallback?pgtId=pgtId&pgtIou=false');

        $this
            ->handleProxyCallback($request)
            ->shouldReturnAnInstanceOf(ResponseInterface::class);

        $this
            ->handleProxyCallback($request)
            ->getStatusCode()
            ->shouldReturn(500);

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

        $response = new Response(200);

        $this
            ->handleProxyCallback($request, [], $response)
            ->shouldReturn($response);
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

    public function it_can_parse_a_bad_proxy_request_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_parse_a_bad_proxy_request_response')
        );

        $this
            ->requestProxyTicket($request)
            ->shouldBeNull();

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_parse_a_good_proxy_request_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_parse_a_good_proxy_request_response')
        );

        $this
            ->requestProxyTicket($request)
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
        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

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
        $from = 'http://local/';

        $request = new Request('GET', $from);

        $parameters = [
            'renew' => true,
        ];

        $this
            ->login($request, $parameters)
            ->getHeader('Location')
            ->shouldReturn(['http://local/cas/login?renew=true&service=http%3A%2F%2Flocal%2F']);

        $request = new Request('GET', $from . '?renew=false');

        $parameters = [
            'renew' => true,
        ];

        $this
            ->login($request, $parameters)
            ->shouldBeNull();
    }

    public function it_can_request_a_proxy_ticket(LoggerInterface $logger, CacheItemPoolInterface $cache)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();

        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_request_a_proxy_ticket')
        );

        $this
            ->requestProxyTicket($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new Request(
            'GET',
            new Uri('http://from/TestClientException')
        );

        $this
            ->requestProxyTicket($request)
            ->shouldBeNull();

        $logger
            ->error('Error during the proxy ticket request.')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_validate_a_bad_proxy_ticket(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
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

        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'POST',
            new Uri('http://from/it_can_validate_a_bad_proxy_ticket')
        );

        $this
            ->requestProxyValidate($request)
            ->shouldBeNull();

        $logger
            ->error('Error during the proxy validate request.')
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_validate_a_bad_service_validate_request(LoggerInterface $logger, CacheItemPoolInterface $cache, CacheItemInterface $cacheItem)
    {
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());
        $psr17Factory = new Psr17Factory();
        $this->beConstructedWith(CasSpecUtils::getTestProperties(), $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'POST',
            new Uri('http://from/it_can_validate_a_bad_service_validate_request')
        );

        $this
            ->requestServiceValidate($request)
            ->shouldBeNull();

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldHaveBeenCalledOnce();
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
        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

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
        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());

        $request = new Request(
            'GET',
            new Uri('http://from/it_can_validate_a_good_service_validate_request')
        );

        $this
            ->requestServiceValidate($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $logger
            ->error('Unable to authenticate the user.')
            ->shouldNotHaveBeenCalled();
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
            ->requestProxyValidate($request)
            ->shouldBeNull();
    }

    public function it_can_validate_any_type_of_ticket()
    {
        $request = new Request('GET', 'http://from?ticket=ST-TICKET-VALID');

        $this
            ->requestTicketValidation($request)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $request = new Request('GET', 'http://from?ticket=PT-TICKET-INVALID');

        $this
            ->requestTicketValidation($request)
            ->shouldBeNull();

        $request = new Request('GET', 'http://from');

        $this
            ->requestTicketValidation($request, [])
            ->shouldBeNull();
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

        $this->beConstructedWith($properties, $client, $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger, new Introspector());
    }
}
