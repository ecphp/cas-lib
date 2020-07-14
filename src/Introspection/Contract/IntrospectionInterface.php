<?php

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
