<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Middleware;

use EcPhp\CasLib\Configuration\Properties as CasProperties;
use EcPhp\CasLib\Middleware\Login;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7Server\ServerRequestCreator;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;

class LoginSpec extends ObjectBehavior
{
    public function it_can_deal_with_array_parameters(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $serverRequest = new ServerRequest('GET', 'http://app');
        $parameters = [
            'custom' => range(1, 5),
        ];

        $this->beConstructedWith($parameters, Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->handle($serverRequest)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $this
            ->handle($serverRequest)
            ->getHeaderLine('Location')
            ->shouldReturn('http://local/cas/login?custom%5B0%5D=1&custom%5B1%5D=2&custom%5B2%5D=3&custom%5B3%5D=4&custom%5B4%5D=5&service=http%3A%2F%2Fapp');
    }

    public function it_can_deal_with_renew_and_gateway_parameters(ServerRequestInterface $serverRequest, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

        $parameters = [
            'renew' => true,
            'gateway' => true,
            'service' => 'service',
        ];

        $this->beConstructedWith($parameters, Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->shouldThrow(Exception::class)
            ->during('handle', [$serverRequest]);

        $logger
            ->error('Unable to get the Login response, gateway and renew parameter cannot be set together.')
            ->shouldHaveBeenCalledOnce();

        $logger
            ->debug(
                'Login parameters are invalid, not redirecting to login page.',
                [
                    'parameters' => [
                        'renew' => true,
                        'gateway' => true,
                        'service' => 'service?gateway=0&renew=0',
                    ],
                    'validatedParameters' => null,
                ]
            )
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_deal_with_renew_parameter(ServerRequestInterface $serverRequest, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();

        $serverRequest = new ServerRequest('GET', 'http://app');

        $parameters = [
            'renew' => 'coin',
            'gateway' => false,
        ];

        $this->beConstructedWith($parameters, Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->handle($serverRequest)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $logger
            ->debug(
                'Building service response redirection to {url}.',
                [
                    'url' => 'http://local/cas/login?renew=true&service=http%3A%2F%2Fapp%3Frenew%3D0',
                ]
            )
            ->shouldHaveBeenCalledOnce();
    }

    public function it_can_get_a_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->handle($creator->fromGlobals())
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_is_initializable(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $this->beConstructedWith([], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this->shouldHaveType(Login::class);
    }

    public function it_make_sure_that_a_user_parameter_take_precedence_on_configuration(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $serverRequest = new ServerRequest('GET', 'http://app');
        $parameters = [
            'service' => 'fooooooo',
        ];

        $properties = new CasProperties([
            'base_url' => 'http://local/cas',
            'protocol' => [
                'login' => [
                    'path' => '/login',
                    'allowed_parameters' => [
                        'service',
                    ],
                    'default_parameters' => [
                        'service' => 'http://bar.foo',
                    ],
                ],
            ],
        ]);

        $this->beConstructedWith($parameters, $properties, $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->handle($serverRequest)
            ->shouldBeAnInstanceOf(ResponseInterface::class);

        $this
            ->handle($serverRequest)
            ->getHeaderLine('Location')
            ->shouldReturn('http://local/cas/login?service=fooooooo');
    }
}
