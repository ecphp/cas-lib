<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Redirect;

use EcPhp\CasLib\Utils\Uri;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_key_exists;

final class Login extends Redirect implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $defaultParameters = $this->getParameters() +
            [
                'service' => (string) $request->getUri(),
            ];

        $parameters = $this->formatProtocolParameters($defaultParameters);

        if (null === $validatedParameters = $this->validate($request, $parameters)) {
            $this
                ->getLogger()
                ->debug(
                    'Login parameters are invalid, not redirecting to login page.',
                    [
                        'parameters' => $parameters,
                        'validatedParameters' => $validatedParameters,
                    ]
                );

            throw new Exception('Login parameters are invalid, not redirecting to login page.');
        }

        return $this->createRedirectResponse(
            (string) $this->buildUri(
                $request->getUri(),
                'login',
                $validatedParameters
            )
        );
    }

    protected function formatProtocolParameters(array $parameters): array
    {
        $parameters = parent::formatProtocolParameters($parameters);
        $parametersToSetToZero = [];

        foreach (['gateway', 'renew'] as $queryParameter) {
            if (false === array_key_exists($queryParameter, $parameters)) {
                continue;
            }

            $parameters[$queryParameter] = 'true';
            $parametersToSetToZero[] = $queryParameter;
        }

        if (true === array_key_exists('service', $parameters)) {
            $service = $this->getUriFactory()->createUri($parameters['service']);

            foreach ($parametersToSetToZero as $parameterToSetToZero) {
                $service = Uri::withParam($service, $parameterToSetToZero, '0');
            }

            $parameters['service'] = (string) $service;
        }

        return $parameters;
    }

    protected function getProtocolProperties(): array
    {
        return $this->getProperties()['protocol']['login'] ?? [];
    }

    /**
     * @param string[] $parameters
     *
     * @return string[]|null
     */
    private function validate(RequestInterface $request, array $parameters): ?array
    {
        $uri = $request->getUri();

        $renew = $parameters['renew'] ?? false;
        $gateway = $parameters['gateway'] ?? false;

        if ('true' === $renew && 'true' === $gateway) {
            $this
                ->getLogger()
                ->error('Unable to get the Login response, gateway and renew parameter cannot be set together.');

            return null;
        }

        foreach (['gateway', 'renew'] as $queryParameter) {
            if (false === array_key_exists($queryParameter, $parameters)) {
                continue;
            }

            if ('true' !== Uri::getParam($uri, $queryParameter, 'true')) {
                return null;
            }
        }

        return $parameters;
    }
}
