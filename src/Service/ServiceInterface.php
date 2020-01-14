<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Service;

use EcPhp\CasLib\Handler\HandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface ServiceInterface.
 */
interface ServiceInterface extends HandlerInterface
{
    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getCredentials(ResponseInterface $response): ?ResponseInterface;
}
