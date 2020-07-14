<?php

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Introspection;

use EcPhp\CasLib\Introspection\Contract\IntrospectionInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class ServiceValidateSpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_service_validate_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
 <cas:authenticationSuccess>
  <cas:user>user</cas:user>
  <cas:proxyGrantingTicket>proxyGrantingTicket</cas:proxyGrantingTicket>
  <cas:proxies>
    <cas:proxy>http://proxy1</cas:proxy>
    <cas:proxy>http://proxy2</cas:proxy>
  </cas:proxies>
 </cas:authenticationSuccess>
</cas:serviceResponse>
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

    public function it_can_use_the_withParsedResponse_wither_method()
    {
        $psr17Factory = new Psr17Factory();
        $body = 'body';

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->beConstructedWith([], 'XML', $response);

        $this
            ->withParsedResponse(['foo'])
            ->shouldNotReturn($this);

        $this
            ->withParsedResponse(['foo'])
            ->shouldReturnAnInstanceOf(IntrospectionInterface::class);
    }
}
