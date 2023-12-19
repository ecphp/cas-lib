<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\CasLib\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class TestClientException extends Exception implements ClientExceptionInterface {}
