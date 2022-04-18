<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract\Response;

use EcPhp\CasLib\Contract\Response\Type\AuthenticationFailure;
use EcPhp\CasLib\Contract\Response\Type\Proxy;
use EcPhp\CasLib\Contract\Response\Type\ProxyFailure;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use EcPhp\CasLib\Exception\CasResponseBuilderException;
use Psr\Http\Message\ResponseInterface;

interface CasResponseBuilderInterface
{
    public function createAuthenticationFailure(ResponseInterface $response): AuthenticationFailure;

    public function createProxyFailure(ResponseInterface $response): ProxyFailure;

    public function createProxySuccess(ResponseInterface $response): Proxy;

    public function createServiceValidate(ResponseInterface $response): ServiceValidate;

    /**
     * @throws CasResponseBuilderException
     */
    public function fromResponse(ResponseInterface $response): CasResponseInterface;
}
