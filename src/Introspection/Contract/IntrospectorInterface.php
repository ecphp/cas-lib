<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

use Psr\Http\Message\ResponseInterface;

interface IntrospectorInterface
{
    public function detect(ResponseInterface $response): IntrospectionInterface;

    /**
     * @return array<string, string|array<mixed>
     */
    public function parse(ResponseInterface $response, string $format = 'XML'): array;
}
