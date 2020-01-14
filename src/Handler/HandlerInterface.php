<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Handler;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface HandlerInterface.
 */
interface HandlerInterface
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(): ?ResponseInterface;
}
