<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Response\Factory;

use EcPhp\CasLib\Contract\Response\CasResponseInterface;
use EcPhp\CasLib\Contract\Response\Factory\CasResponseFactoryInterface;
use EcPhp\CasLib\Response\Type\ServiceValidate;
use Psr\Http\Message\ResponseInterface;

final class ServiceValidateFactory implements CasResponseFactoryInterface
{
    public function decorate(ResponseInterface $response): CasResponseInterface
    {
        return new ServiceValidate($response);
    }
}
