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
use EcPhp\CasLib\Response\Factory\AuthenticationFailureFactory as FactoryAuthenticationFailureFactory;
use EcPhp\CasLib\Response\Factory\ProxyFactory as FactoryProxyFactory;
use EcPhp\CasLib\Response\Factory\ProxyFailureFactory as FactoryProxyFailureFactory;
use EcPhp\CasLib\Response\Factory\ServiceValidateFactory as FactoryServiceValidateFactory;
use EcPhp\CasLib\Utils\Response as ResponseUtils;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;

final class CasResponseBuilder implements CasResponseBuilderInterface
{
    public function __construct(
        private readonly AuthenticationFailureFactory $authenticationFailureFactory = new FactoryAuthenticationFailureFactory(),
        private readonly ProxyFactory $proxyFactory = new FactoryProxyFactory(),
        private readonly ProxyFailureFactory $proxyFailureFactory = new FactoryProxyFailureFactory(),
        private readonly ServiceValidateFactory $serviceValidateFactory = new FactoryServiceValidateFactory()
    ) {}

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
