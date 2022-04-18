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

use function array_key_exists;

final class Login extends Handler implements HandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        $parameters = $this->getParameters();
        $parameters += Uri::getParams($request->getUri());
        $parameters += $this->getProperties()['protocol'][HandlerInterface::TYPE_LOGIN]['default_parameters'] ?? [];
        $parameters += [
            'service' => (string) $request->getUri(),
        ];
        $parameters = $this->formatProtocolParameters($parameters);

        $this->validate($request, $parameters);

        $uri = $this
            ->buildUri(
                $request->getUri(),
                HandlerInterface::TYPE_LOGIN,
                $parameters
            );

        return $this
            ->getPsr17()
            ->createResponse(302)
            ->withHeader(
                'Location',
                (string) $uri
            );
    }

    /**
     * @param string[] $parameters
     *
     * @return string[]
     */
    private function validate(
        RequestInterface $request,
        array $parameters
    ): void {
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
    }
}
