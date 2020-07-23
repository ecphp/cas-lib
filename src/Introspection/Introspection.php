<?php

declare(strict_types=1);

namespace EcPhp\CasLib\Introspection;

use Psr\Http\Message\ResponseInterface;

abstract class Introspection
{
    /**
     * @var string
     */
    private $format;

    /**
     * @var array[]
     */
    private $parsedResponse;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * Introspection constructor.
     *
     * @param array[] $parsedResponse
     */
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

    /**
     * @return array[]
     */
    public function getParsedResponse(): array
    {
        return $this->parsedResponse;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
