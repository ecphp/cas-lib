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
                case 'http://local/cas/proxyValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_get_credentials_with_pgtUrl%2Fmissing_pgt':
                case 'http://from/it_can_detect_a_wrong_proxy_response':
                case 'http://local/cas/serviceValidate?format=XML&service=http%3A%2F%2Ffrom&ticket=ST-TICKET-VALID':
                case 'http://local/cas/serviceValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_test_the_proxy_mode_without_pgtUrl&ticket=ST-TICKET-VALID':
                case 'http://local/cas/serviceValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_get_credentials_without_pgtUrl':
                case 'http://local/cas/serviceValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_validate_a_service_ticket':
                    $body = <<< 'EOF'
                            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                            <cas:authenticationSuccess>
                            <cas:user>username</cas:user>
                            </cas:authenticationSuccess>
                            </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://local/cas/serviceValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_validate_a_bad_service_validate_request&ticket=ST-TICKET-INVALID':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                        <cas:authenticationFailure code="INVALID_REQUEST">
                        service and ticket parameters are both required
                        </cas:authenticationFailure>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://local/cas/proxyValidate?format=XML&service=http%3A%2F%2Ffrom&ticket=ST-TICKET-VALID':
                case 'http://local/cas/proxyValidate?format=XML&service=http%3A%2F%2Ffrom&ticket=PT-TICKET-VALID':
                case 'http://local/cas/proxyValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_test_the_proxy_mode_with_pgtUrl&ticket=ST-TICKET-VALID':
                case 'http://local/cas/proxyValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_get_credentials_with_pgtUrl':
                    $body = <<< 'EOF'
                        <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                         <cas:authenticationSuccess>
                          <cas:user>username</cas:user>
                          <cas:proxyGrantingTicket>pgtIou</cas:proxyGrantingTicket>
                         </cas:authenticationSuccess>
                        </cas:serviceResponse>
                        EOF;

                    break;

                case 'http://local/cas/serviceValidate?format=JSON&service=http%3A%2F%2Ffrom%2Fit_can_parse_json_in_a_response':
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

                case 'http://local/cas/proxy?service=service-valid':
                case 'http://local/cas/serviceValidate?format=XML&service=http%3A%2F%2Ffrom%2Fit_can_detect_when_response_type_is_invalid&ticket=ST-TICKET-VALID':
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
                ],
                'logout' => [
                    'path' => '/logout',
                ],
                'serviceValidate' => [
                    'path' => '/serviceValidate',
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
                'proxyValidate' => [
                    'path' => '/proxyValidate',
                    'default_parameters' => [
                        'format' => 'XML',
                    ],
                ],
                'proxy' => [
                    'path' => '/proxy',
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
