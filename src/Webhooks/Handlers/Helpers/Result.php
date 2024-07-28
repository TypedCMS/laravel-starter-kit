<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers;

final class Result
{
    public function __construct(
        protected string $message,
        protected bool $error = false,
    ) { }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isError(): bool
    {
        return $this->error;
    }
}
