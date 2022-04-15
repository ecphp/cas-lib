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
    }
}
