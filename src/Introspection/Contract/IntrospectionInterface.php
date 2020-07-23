<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection\Contract;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface IntrospectionInterface.
 */
interface IntrospectionInterface
{
    public function getFormat(): string;

    /**
     * @return array[]
     */
    public function getParsedResponse(): array;

    public function getResponse(): ResponseInterface;
}
