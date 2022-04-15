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

class ProxySpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_response()
    {
        $psr17Factory = new Psr17Factory();

        $body = <<< 'EOF'
            <?xml version="1.0" encoding="utf-8"?>
            <cas:serviceResponse xmlns:cas="https://ecas.ec.europa.eu/cas/schemas">
            	<cas:proxySuccess>
            		<cas:proxyTicket>PGT-TICKET</cas:proxyTicket>
            	</cas:proxySuccess>
            </cas:serviceResponse>
            EOF;

        $response = (new Response(200))
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($psr17Factory->createStream($body));

        $parsed = [
            'serviceResponse' => [
                'proxySuccess' => [
                    'proxyTicket' => 'PGT-TICKET',
                ],
            ],
        ];

        $this
            ->beConstructedWith($response);

        $this
            ->getProxyTicket()
            ->shouldReturn('PGT-TICKET');

        $this
            ->toArray()
            ->shouldReturn($parsed);
    }
}
