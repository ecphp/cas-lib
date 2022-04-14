<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib;

use EcPhp\CasLib\Configuration\Properties as CasProperties;
use Exception;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use tests\EcPhp\CasLib\Exception\TestClientException;

class Cas extends ObjectBehavior
{
    public static function getHttpClientMock()
    {
        $callback = static function ($method, $url, $options): ResponseInterface {
            $body = '';
            $info = [
                'response_headers' => [
                    'Content-Type' => 'application/xml',
                ],
            ];

            switch ($url) {
                case 'http://from/it_can_validate_a_service_ticket/404':
                    $info = [
                        'http_code' => 404,
                    ];

                    break;

                case 'http://from/?ticket=EMPTY-BODY':
                    $body = '';

                    break;

                case 'http://from/it_can_test_the_proxy_mode_without_pgtUrl':
                case 'http://from/it_can_get_credentials_without_pgtUrl':
                case 'http://from/it_can_validate_a_service_ticket':
                case 'http://from/it_can_validate_a_good_service_validate_request':
                case 'http://from/?ticket=ST-TICKET-VALID':
                    $body = <<< 'EOF'
                            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                            <cas:authenticationSuccess>
                            <cas:user>username</cas:user>
                            </cas:authenticationSuccess>
                            </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/it_can_validate_a_bad_service_validate_request':
                case 'http://from/?ticket=PT-TICKET-INVALID':
                case 'http://from/?ticket=ST-TICKET-INVALID':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationFailure>
                         </cas:authenticationFailure>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/it_can_test_the_proxy_mode_with_pgtUrl':
                case 'http://from/it_can_validate_a_good_proxy_ticket':
                case 'http://from/?ticket=PT-TICKET-VALID':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationSuccess>
                          <cas:user>username</cas:user>
                          <cas:proxies>
                            <cas:proxy>http://app/proxyCallback.php</cas:proxy>
                          </cas:proxies>
                         </cas:authenticationSuccess>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/it_can_get_credentials_with_pgtUrl':
                case 'http://from/?ticket=ST-ticket-pgt':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationSuccess>
                          <cas:user>username</cas:user>
                          <cas:proxyGrantingTicket>pgtIou</cas:proxyGrantingTicket>
                         </cas:authenticationSuccess>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/?ticket=ST-ticket-pgt-pgtiou-not-found':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationSuccess>
                          <cas:user>username</cas:user>
                          <cas:proxyGrantingTicket>unknownPgtIou</cas:proxyGrantingTicket>
                         </cas:authenticationSuccess>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/?ticket=ST-ticket-pgt-pgtiou-pgtid-null':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationSuccess>
                          <cas:user>username</cas:user>
                          <cas:proxyGrantingTicket>pgtIouWithPgtIdNull</cas:proxyGrantingTicket>
                         </cas:authenticationSuccess>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/it_can_validate_a_good_proxy_ticket/2':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationSuccess>
                          <cas:user>username</cas:user>
                          <cas:proxyGrantingTicket>pgtIou</cas:proxyGrantingTicket>
                          <cas:proxies>
                            <cas:proxy>http://app/proxyCallback.php</cas:proxy>
                          </cas:proxies>
                         </cas:authenticationSuccess>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/it_can_parse_json_in_a_response':
                    $info = [
                        'response_headers' => [
                            'Content-Type' => 'application/json',
                        ],
                    ];

                    $body = <<< 'EOF'
                        {
                            "serviceResponse": {
                                "authenticationSuccess": {
                                    "user": "username"
                                }
                            }
                        }
                        EOF;

                    break;

                case 'http://from/it_can_request_a_proxy_ticket':
                case 'http://from/it_can_parse_a_good_proxy_request_response':
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

                    break;

                case 'http://from/it_can_parse_a_bad_proxy_request_response':
                    $body = <<< 'EOF'
                        <?xml version="1.0" encoding="utf-8"?>
                        <cas:serviceResponse xmlns:cas="https://ecas.ec.europa.eu/cas/schemas"
                                             server="ECAS MOCKUP version 4.6.0.20924 - 09/02/2016 - 14:37"
                                             date="2019-10-18T12:17:53.069+02:00" version="4.5">
                        	<cas:proxyFailure>
                        	TODO: Find something to put here.
                            </cas:proxyFailure>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://from/it_can_validate_a_bad_proxy_ticket':
                case 'http://from/TestClientException':
                case 'http://from/?ticket=BAD-http-query':
                    throw new TestClientException();

                    break;

                case 'https://example.com/error':
                    break;

                default:
                    throw new Exception(sprintf('URL %s is not defined in the HTTP mock client.', $url));

                    break;
            }

            return new MockResponse($body, $info);
        };

        return new MockHttpClient($callback);
    }

    public static function getTestProperties(): CasProperties
    {
        return new CasProperties([
            'base_url' => 'http://local/cas',
            'protocol' => [
                'login' => [
                    'path' => '/login',
                    'allowed_parameters' => [
                        'service',
                        'custom',
                        'renew',
                        'gateway',
                    ],
                ],
                'logout' => [
                    'path' => '/logout',
                    'allowed_parameters' => [
                        'service',
                        'custom',
                    ],
                ],
                'serviceValidate' => [
                    'path' => '/serviceValidate',
                    'allowed_parameters' => [
                        'ticket',
                        'service',
                        'custom',
                    ],
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
                'proxyValidate' => [
                    'path' => '/proxyValidate',
                    'allowed_parameters' => [
                        'ticket',
                        'service',
                        'custom',
                    ],
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
                'proxy' => [
                    'path' => '/proxy',
                    'allowed_parameters' => [
                        'targetService',
                        'pgt',
                    ],
                ],
            ],
        ]);
    }

    public static function getTestPropertiesWithPgtUrl(): CasProperties
    {
        $properties = self::getTestProperties()->all();

        $properties['protocol']['serviceValidate']['default_parameters']['pgtUrl'] = 'https://from/proxyCallback.php';

        return new CasProperties($properties);
    }
}
