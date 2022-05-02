<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Utils;

use EcPhp\CasLib\Exception\CasExceptionInterface;
use Nyholm\Psr7\Response;
use PhpSpec\ObjectBehavior;

class ResponseSpec extends ObjectBehavior
{
    public function it_convert_a_json_response()
    {
        $body = <<< 'EOF'
            {
                "serviceResponse": {
                    "authenticationSuccess": {
                        "user": "username"
                    }
                }
            }
            EOF;

        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            $body
        );

        $this
            ->toArray($response)
            ->shouldReturn([
                'serviceResponse' => [
                    'authenticationSuccess' => [
                        'user' => 'username',
                    ],
                ],
            ]);
    }

    public function it_convert_a_xml_response()
    {
        $body = <<< 'EOF'
                <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                    <cas:authenticationSuccess>
                        <cas:user>username</cas:user>
                    </cas:authenticationSuccess>
                </cas:serviceResponse>
            EOF;

        $response = new Response(
            200,
            [
                'Content-Type' => 'application/xml',
            ],
            $body
        );

        $this
            ->toArray($response)
            ->shouldReturn([
                'serviceResponse' => [
                    'authenticationSuccess' => [
                        'user' => 'username',
                    ],
                ],
            ]);
    }

    public function it_throws_when_body_is_empty()
    {
        $response = new Response(
            200,
            [],
            ''
        );

        $this
            ->shouldThrow(CasExceptionInterface::class)
            ->during('toArray', [$response]);
    }

    public function it_throws_when_content_type_header_is_missing()
    {
        $body = <<< 'EOF'
                <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                    <cas:authenticationSuccess>
                        <cas:user>username</cas:user>
                    </cas:authenticationSuccess>
                </cas:serviceResponse>
            EOF;

        $response = new Response(
            200,
            [],
            $body
        );

        $this
            ->shouldThrow(CasExceptionInterface::class)
            ->during('toArray', [$response]);
    }
}
