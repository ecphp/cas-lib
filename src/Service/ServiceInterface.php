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
    public function getCredentials(ResponseInterface $response): ?ResponseInterface;
}
