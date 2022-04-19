<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Response\Type;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class ServiceValidateSpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_service_validate_response()
    {
        $psr17Factory = new Psr17Factory();

        $bodyArray = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'user' => 'user',
                    'proxyGrantingTicket' => 'proxyGrantingTicket',
                    'proxies' => [
                        'proxy' => [
                            'http://proxy1',
                            'http://proxy2',
                        ],
                    ],
                    'extendedAttributes' => [
                        'extendedAttribute' => [
                            'attributeValue' => [
                                0 => 'rex',
                                1 => 'snoopy',
                            ],
                            '@attributes' => [
                                'name' => 'http://stork.eu/motherInLawDogName',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr17Factory->createStream(json_encode($bodyArray)));

        $this
            ->beConstructedWith($response);

        $this
            ->getCredentials()
            ->shouldReturn(
                $bodyArray['serviceResponse']['authenticationSuccess']
            );

        $this
            ->getProxies()
            ->shouldReturn([
                'proxy' => [
                    'http://proxy1',
                    'http://proxy2',
                ],
            ]);

        $this
            ->toArray()
            ->shouldReturn($bodyArray);
    }

    public function it_can_detect_a_service_validate_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationSuccess>
              <cas:user>user</cas:user>
              <cas:proxyGrantingTicket>proxyGrantingTicket</cas:proxyGrantingTicket>
             </cas:authenticationSuccess>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $parsed = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'user' => 'user',
                    'proxyGrantingTicket' => 'proxyGrantingTicket',
                ],
            ],
        ];

        $this
            ->beConstructedWith($response);

        $this
            ->toArray()
            ->shouldReturn($parsed);

        $this
            ->getProxies()
            ->shouldReturn([]);

        $this
            ->toArray()
            ->shouldReturn($parsed);

        $this
            ->getBody()
            ->__toString()
            ->shouldReturn($body);

        $this
            ->getHeader('Content-Type')
            ->shouldReturn(['application/xml']);

        $this
            ->withHeader('Content-Type', 'application/html')
            ->getHeader('Content-Type')
            ->shouldReturn(['application/html']);

        $this
            ->getHeaderLine('Content-Type')
            ->shouldReturn('application/xml');

        $this
            ->getHeaders()
            ->shouldReturn([
                'Content-Type' => [
                    'application/xml',
                ],
            ]);

        $this
            ->getProtocolVersion()
            ->shouldReturn('1.1');

        $this
            ->getReasonPhrase()
            ->shouldReturn('OK');

        $this
            ->getStatusCode()
            ->shouldReturn(200);

        $this
            ->hasHeader('Content-Type')
            ->shouldReturn(true);

        $this
            ->hasHeader('foobar')
            ->shouldReturn(false);

        $this
            ->withAddedHeader('foobar', 'barfoo')
            ->hasHeader('foobar')
            ->shouldReturn(true);

        $this
            ->withBody($psr17Factory->createStream('foobar'))
            ->getBody()
            ->__toString()
            ->shouldReturn('foobar');

        $this
            ->withoutHeader('Content-Type')
            ->hasHeader('Content-Type')
            ->shouldReturn(false);

        $this
            ->withProtocolVersion('2.0')
            ->getProtocolVersion()
            ->shouldReturn('2.0');

        $this
            ->withStatus(500, 'foo')
            ->getStatusCode()
            ->shouldReturn(500);

        $this
            ->withStatus(500, 'foo')
            ->getReasonPhrase()
            ->shouldReturn('foo');
    }
}
