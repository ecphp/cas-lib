<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Introspection;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class ServiceValidateSpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_service_validate_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            Useless stuff here.
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $credentials = [
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
        ];

        $parsed = [
            'serviceResponse' => [
                'authenticationSuccess' => $credentials,
            ],
        ];

        $this
            ->beConstructedWith($parsed, 'XML', $response);

        $this
            ->getCredentials()
            ->shouldReturn(
                $credentials
            );

        $this
            ->getFormat()
            ->shouldReturn('XML');

        $this
            ->getProxies()
            ->shouldReturn([
                'proxy' => [
                    'http://proxy1',
                    'http://proxy2',
                ],
            ]);

        $this
            ->getResponse()
            ->shouldReturn($response);

        $this
            ->getParsedResponse()
            ->shouldReturn($parsed);
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

        $credentials = [
            'user' => 'user',
            'proxyGrantingTicket' => 'proxyGrantingTicket',
        ];

        $parsed = [
            'serviceResponse' => [
                'authenticationSuccess' => [
                    'user' => 'user',
                    'proxyGrantingTicket' => 'proxyGrantingTicket',
                ],
            ],
        ];

        $this
            ->beConstructedWith($parsed, 'XML', $response);

        $this
            ->getCredentials()
            ->shouldReturn($credentials);

        $this
            ->getFormat()
            ->shouldReturn('XML');

        $this
            ->getProxies()
            ->shouldReturn([]);

        $this
            ->getResponse()
            ->shouldReturn($response);
    }
}
