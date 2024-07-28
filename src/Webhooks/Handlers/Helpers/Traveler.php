<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers;

final class Traveler
{
    /**
     * @var array<Result>
     */
    protected array $results = [];

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(protected array $payload) { }

    public function getDomain(): string
    {
        return $this->getPayload()['domain'];
    }

    public function getEvent(): string
    {
        return $this->getPayload()['event'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function addResult(string $message, bool $error = false): Result
    {
        return $this->results[] = new Result($message, $error);
    }

    /**
     * @return array<Result>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
