<?php

declare(strict_types=1);

namespace spec\tests\EcPhp\CasLib;

use EcPhp\CasLib\Cas;
use EcPhp\CasLib\Introspection\Introspector;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PhpSpec\ObjectBehavior;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use spec\EcPhp\CasLib\Cas as CasSpecUtils;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Psr18Client;

class CasSpec extends ObjectBehavior
{
    public function it_can_test_the_proxy_mode_with_pgtUrl()
    {
        $properties = CasSpecUtils::getTestPropertiesWithPgtUrl();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

        $introspector = new Introspector();

        $this->beConstructedWith(new Cas(
            $serverRequest,
            $properties,
            $client,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            new ArrayAdapter(),
            new NullLogger(),
            $introspector
        ));

        $this
            ->requestTicketValidation(['service' => 'service', 'ticket' => 'ticket'], null)
            ->getBody()
            ->__toString()
            ->shouldReturn('{"serviceResponse":{"authenticationSuccess":{"user":"username","proxies":{"proxy":"http:\/\/app\/proxyCallback.php"}}}}');
    }

    public function it_can_test_the_proxy_mode_without_pgtUrl(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $properties = CasSpecUtils::getTestProperties();
        $client = new Psr18Client(CasSpecUtils::getHttpClientMock());

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        $serverRequest = $creator->fromGlobals();

        $introspector = new Introspector();

        $this->beConstructedWith(new Cas(
            $serverRequest,
            $properties,
            $client,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            new ArrayAdapter(),
            new NullLogger(),
            $introspector
        ));

        $this
            ->requestTicketValidation(['service' => 'service', 'ticket' => 'ticket'], null)
            ->getBody()
            ->__toString()
            ->shouldReturn('{"serviceResponse":{"authenticationSuccess":{"user":"username"}}}');
    }
}
