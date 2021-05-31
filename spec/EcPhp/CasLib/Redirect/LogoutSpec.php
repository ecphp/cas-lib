<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Redirect;

use EcPhp\CasLib\Redirect\Logout;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use spec\EcPhp\CasLib\Cas;

class LogoutSpec extends ObjectBehavior
{
    public function it_can_get_a_response(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $this->beConstructedWith($creator->fromGlobals(), [], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this
            ->handle()
            ->shouldBeAnInstanceOf(ResponseInterface::class);
    }

    public function it_is_initializable(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $this->beConstructedWith($creator->fromGlobals(), [], Cas::getTestProperties(), $psr17Factory, $psr17Factory, $psr17Factory, $cache, $logger);

        $this->shouldHaveType(Logout::class);
    }
}
