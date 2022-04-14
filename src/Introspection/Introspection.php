<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use Psr\Http\Message\ResponseInterface;

abstract class Introspection
{
    private string $format;

    private array $parsedResponse;

    private ResponseInterface $response;

    public function __construct(array $parsedResponse, string $format, ResponseInterface $response)
    {
        $this->response = $response;
        $this->parsedResponse = $parsedResponse;
        $this->format = $format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getParsedResponse(): array
    {
        return $this->parsedResponse;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
