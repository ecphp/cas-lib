<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Response;

use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure;
use EcPhp\CasLib\Contract\Response\Type\Proxy;
use EcPhp\CasLib\Contract\Response\Type\ProxyFailure;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use EcPhp\CasLib\Response\CasResponseBuilderInterface;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class CasResponseBuilderSpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_failure_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:proxyFailure>
                unrecognized pgt: 'PGT-123'
             </cas:proxyFailure>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->fromResponse($response)
            ->shouldImplement(ProxyFailure::class);
    }

    public function it_can_detect_a_proxy_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            <?xml version="1.0" encoding="utf-8"?>
            <cas:serviceResponse xmlns:cas="https://ecas.ec.europa.eu/cas/schemas"
                                 server="ECAS MOCKUP version 4.6.0.20924 - 09/02/2016 - 14:37"
                                 date="2019-10-18T12:17:53.069+02:00" version="4.5">
            	<cas:proxySuccess>
            		<cas:proxyTicket>PT-214-A3OoEPNr4Q9kNNuYzmfN8azU31aDUsuW8nk380k7wDExT5PFJpxR1TrNI3q3VGzyDdi0DpZ1LKb8IhPKZKQvavW-8hnfexYjmLCx7qWNsLib1W-DCzzoLVTosAUFzP3XDn5dNzoNtxIXV9KSztF9fYhwHvU0</cas:proxyTicket>
            	</cas:proxySuccess>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->fromResponse($response)
            ->shouldBeAnInstanceOf(Proxy::class);
    }

    public function it_can_detect_a_service_validate_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationSuccess>
              <cas:user>username</cas:user>
              <cas:proxyGrantingTicket>false</cas:proxyGrantingTicket>
             </cas:authenticationSuccess>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->fromResponse($response)
            ->shouldBeAnInstanceOf(ServiceValidate::class);

        $body = <<< 'EOF'
            {
                "serviceResponse": {
                    "authenticationSuccess": {
                        "user": "username"
                    }
                }
            }
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->fromResponse($response)
            ->shouldBeAnInstanceOf(ServiceValidate::class);
    }

    public function it_can_detect_a_service_validate_response_with_proxy()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationSuccess>
              <cas:user>user</cas:user>
              <cas:proxyGrantingTicket>proxyGrantingTicket</cas:proxyGrantingTicket>
              <cas:proxies>
                <cas:proxy>https://ecasclient/proxyCallback.php</cas:proxy>
              </cas:proxies>
             </cas:authenticationSuccess>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->fromResponse($response)
            ->shouldBeAnInstanceOf(ServiceValidate::class);
    }

    public function it_can_detect_a_wrong_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            FOO
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->shouldThrow(Exception::class)
            ->during('fromResponse', [$response]);

        $body = <<< 'EOF'
            FOO
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->shouldThrow(Exception::class)
            ->during('fromResponse', [$response]);
    }

    public function it_can_detect_an_authentication_failure_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<<'EOF'
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
             <cas:authenticationFailure code="INVALID_TICKET">
                Ticket ST-1856339-aA5Yuvrxzpv8Tau1cYQ7 not recognized
              </cas:authenticationFailure>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->fromResponse($response)
            ->shouldBeAnInstanceOf(AuthenticationFailure::class);
    }

    public function it_can_detect_an_unknown_type_of_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            {
                "foo": {
                    "bar": {
                        "user": "username"
                    }
                }
            }
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->shouldThrow(Exception::class)
            ->during('fromResponse', [$response]);
    }

    public function it_can_detect_an_unsupported_parse_format()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            FOO
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr17Factory->createStream($body));

        $this
            ->shouldThrow(Exception::class)
            ->during('fromResponse', [$response, 'FOOBAR']);
    }

    public function it_can_detect_the_type_of_a_response()
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
            ->fromResponse($response)
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
            ->shouldThrow(Exception::class)
            ->during('fromResponse', [$response]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CasResponseBuilderInterface::class);
    }
}
