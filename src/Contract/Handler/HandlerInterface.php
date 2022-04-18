<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Contract\Handler;

use Psr\Http\Server\RequestHandlerInterface;

interface HandlerInterface extends RequestHandlerInterface
{
    public const TYPE_LOGIN = 'login';

    public const TYPE_LOGOUT = 'logout';

    public const TYPE_PROXY = 'proxy';

    public const TYPE_PROXY_VALIDATE = 'proxyValidate';

    public const TYPE_SERVICE_VALIDATE = 'serviceValidate';
}
