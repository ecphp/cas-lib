<?php

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
