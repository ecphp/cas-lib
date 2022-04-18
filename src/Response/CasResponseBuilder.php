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
use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure as TypeAuthenticationFailure;
use EcPhp\CasLib\Contract\Response\Type\Proxy as TypeProxy;
use EcPhp\CasLib\Contract\Response\Type\ProxyFailure as TypeProxyFailure;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate as TypeServiceValidate;
use EcPhp\CasLib\Exception\CasResponseBuilderException;
use EcPhp\CasLib\Response\Type\AuthenticationFailure;
use EcPhp\CasLib\Response\Type\Proxy;
use EcPhp\CasLib\Response\Type\ProxyFailure;
use EcPhp\CasLib\Response\Type\ServiceValidate;
use EcPhp\CasLib\Utils\Response as ResponseUtils;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;

// phpcs:disable Generic.Files.LineLength.TooLong

final class CasResponseBuilder implements CasResponseBuilderInterface
{
    public function createAuthenticationFailure(ResponseInterface $response): TypeAuthenticationFailure
    {
        return new AuthenticationFailure($response);
    }

    public function createProxyFailure(ResponseInterface $response): TypeProxyFailure
    {
        return new ProxyFailure($response);
    }

    public function createProxySuccess(ResponseInterface $response): TypeProxy
    {
        return new Proxy($response);
    }

    public function createServiceValidate(ResponseInterface $response): TypeServiceValidate
    {
        return new ServiceValidate($response);
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
            return $this->createAuthenticationFailure($response);
        }

        if (array_key_exists('proxyFailure', $data['serviceResponse'])) {
            return $this->createProxyFailure($response);
        }

        if (array_key_exists('authenticationSuccess', $data['serviceResponse'])) {
            return $this->createServiceValidate($response);
        }

        if (array_key_exists('proxySuccess', $data['serviceResponse'])) {
            return $this->createProxySuccess($response);
        }

        throw CasResponseBuilderException::unknownResponseType();
    }
}
