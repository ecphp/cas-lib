<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Response\Type;

use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class ProxyFailureSpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_failure_response()
    {
        $body = [
            'serviceResponse' => [
                'proxyFailure' => "unrecognized pgt: 'PGT-123'",
            ],
        ];

        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            json_encode($body)
        );

        $parsed = [
            'serviceResponse' => [
                'proxyFailure' => "unrecognized pgt: 'PGT-123'",
            ],
        ];

        $this
            ->beConstructedWith($response);

        $this
            ->getMessage()
            ->shouldReturn("unrecognized pgt: 'PGT-123'");

        $this
            ->toArray()
            ->shouldReturn($parsed);
    }
}
