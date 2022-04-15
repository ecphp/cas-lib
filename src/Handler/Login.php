<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use EcPhp\CasLib\Contract\Handler\HandlerInterface;
use EcPhp\CasLib\Exception\CasHandlerException;
use EcPhp\CasLib\Utils\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;

final class Login extends Handler implements HandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        $parameters = $this
            ->formatProtocolParameters(
                $this->getParameters($request)
            );

        return $this
            ->getPsr17()
            ->createResponse(302)
            ->withHeader(
                'Location',
                (string) $this
                    ->buildUri(
                        $request->getUri(),
                        'login',
                        $this->validate($request, $parameters)
                    )
            );
    }

    protected function formatProtocolParameters(array $parameters): array
    {
        $parameters = parent::formatProtocolParameters($parameters);

        foreach (['gateway', 'renew'] as $queryParameter) {
            if (false === array_key_exists($queryParameter, $parameters)) {
                continue;
            }

            $parameters[$queryParameter] = 'true';
        }

        return $parameters;
    }

    protected function getProtocolProperties(UriInterface $uri): array
    {
        $protocolProperties = $this->getProperties()['protocol']['login'] ?? [];

        $protocolProperties['default_parameters'] += [
            'service' => (string) $uri,
        ];

        return $protocolProperties;
    }

    /**
     * @param string[] $parameters
     *
     * @return string[]
     */
    private function validate(
        RequestInterface $request,
        array $parameters
    ): array {
        $uri = $request->getUri();

        $renew = $parameters['renew'] ?? false;
        $gateway = $parameters['gateway'] ?? false;

        if ('true' === $renew && 'true' === $gateway) {
            throw CasHandlerException::loginRenewAndGatewayParametersAreSet();
        }

        foreach (['gateway', 'renew'] as $queryParameter) {
            if (false === array_key_exists($queryParameter, $parameters)) {
                continue;
            }

            if ('true' !== Uri::getParam($uri, $queryParameter, 'true')) {
                throw CasHandlerException::loginInvalidParameters();
            }
        }

        return $parameters;
    }
}
