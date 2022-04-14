<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response;

use EcPhp\CasLib\Contract\Response\CasResponseInterface;
use EcPhp\CasLib\Response\Type\AuthenticationFailure;
use EcPhp\CasLib\Response\Type\Proxy;
use EcPhp\CasLib\Response\Type\ProxyFailure;
use EcPhp\CasLib\Response\Type\ServiceValidate;
use EcPhp\CasLib\Utils\Response as ResponseUtils;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;

// phpcs:disable Generic.Files.LineLength.TooLong

final class CasResponseBuilder implements CasResponseBuilderInterface
{
    public function fromResponse(ResponseInterface $response): CasResponseInterface
    {
        if (200 !== $response->getStatusCode()) {
            throw new Exception('Invalid status code.');
        }

        $data = (new ResponseUtils())->toArray($response);

        if (false === array_key_exists('serviceResponse', $data)) {
            throw new Exception('Invalid CAS response type.');
        }

        if (array_key_exists('authenticationFailure', $data['serviceResponse'])) {
            return new AuthenticationFailure($response);
        }

        if (array_key_exists('proxyFailure', $data['serviceResponse'])) {
            return new ProxyFailure($response);
        }

        if (array_key_exists('authenticationSuccess', $data['serviceResponse']) && array_key_exists('user', $data['serviceResponse']['authenticationSuccess'])) {
            return new ServiceValidate($response);
        }

        if (array_key_exists('proxySuccess', $data['serviceResponse']) && array_key_exists('proxyTicket', $data['serviceResponse']['proxySuccess'])) {
            return new Proxy($response);
        }

        throw new InvalidArgumentException('Unknown CAS response type.');
    }
}
