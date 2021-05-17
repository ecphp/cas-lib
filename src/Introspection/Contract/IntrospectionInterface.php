<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

use Psr\Http\Message\ResponseInterface;

interface IntrospectionInterface
{
    public function getFormat(): string;

    public function getParsedResponse(): array;

    public function getResponse(): ResponseInterface;

    public function withParsedResponse(array $parsedResponse): IntrospectionInterface;
}
