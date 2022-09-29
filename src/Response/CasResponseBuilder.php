<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Contract\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Contract\Response\CasResponseInterface;
use EcPhp\CasLib\Contract\Response\Factory\AuthenticationFailureFactory;
use EcPhp\CasLib\Contract\Response\Factory\ProxyFactory;
use EcPhp\CasLib\Contract\Response\Factory\ProxyFailureFactory;
use EcPhp\CasLib\Contract\Response\Factory\ServiceValidateFactory;
use EcPhp\CasLib\Exception\CasResponseBuilderException;
use EcPhp\CasLib\Utils\Response as ResponseUtils;
use loophp\psr17\Psr17Interface;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;

// phpcs:disable Generic.Files.LineLength.TooLong

final class CasResponseBuilder implements CasResponseBuilderInterface
{
    private AuthenticationFailureFactory $authenticationFailureFactory;

    private ProxyFactory $proxyFactory;

    private ProxyFailureFactory $proxyFailureFactory;

    private Psr17Interface $psr17;

    private ServiceValidateFactory $serviceValidateFactory;

    public function __construct(
        Psr17Interface $psr17,
        AuthenticationFailureFactory $authenticationFailureFactory,
        ProxyFactory $proxyFactory,
        ProxyFailureFactory $proxyFailureFactory,
        ServiceValidateFactory $serviceValidateFactory
    ) {
        $this->psr17 = $psr17;
        $this->authenticationFailureFactory = $authenticationFailureFactory;
        $this->proxyFactory = $proxyFactory;
        $this->proxyFailureFactory = $proxyFailureFactory;
        $this->serviceValidateFactory = $serviceValidateFactory;
    }

    public function fromResponse(ResponseInterface $response): CasResponseInterface
    {
        if (200 !== $code = $response->getStatusCode()) {
            throw CasResponseBuilderException::invalidStatusCode($code);
        }

        $data = (new ResponseUtils())->toArray($response);

        if (false === array_key_exists('serviceResponse', $data)) {
            throw CasResponseBuilderException::invalidResponseType();
        }

        if (array_key_exists('authenticationFailure', $data['serviceResponse'])) {
            return $this->authenticationFailureFactory->decorate($response);
        }

        if (array_key_exists('proxyFailure', $data['serviceResponse'])) {
            return $this->proxyFailureFactory->decorate($response);
        }

        if (array_key_exists('authenticationSuccess', $data['serviceResponse'])) {
            return $this->serviceValidateFactory->decorate($response);
        }

        if (array_key_exists('proxySuccess', $data['serviceResponse'])) {
            return $this->proxyFactory->decorate($response);
        }

        throw CasResponseBuilderException::unknownResponseType();
    }
}
