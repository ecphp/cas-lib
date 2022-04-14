<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Introspection;

use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class ProxyFailureSpec extends ObjectBehavior
{
    public function it_can_detect_a_proxy_failure_response()
    {
        $response = (new Response(200));

        $parsed = [
            'serviceResponse' => [
                'proxyFailure' => "unrecognized pgt: 'PGT-123'",
            ],
        ];

        $this
            ->beConstructedWith($parsed, 'XML', $response);

        $this
            ->getMessage()
            ->shouldReturn("unrecognized pgt: 'PGT-123'");

        $this
            ->getResponse()
            ->shouldReturn($response);
    }
}
