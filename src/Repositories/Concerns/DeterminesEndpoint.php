<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

trait DeterminesEndpoint
{
    public static string $apiEndpoint = 'https://api.tcms.io/';

    public static string $mapiEndpoint = 'https://mapi.tcms.io/';

    protected bool $forceMapi = false;

    /**
     * @return $this
     */
    public function mapi(): static
    {
        $this->forceMapi = true;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->getBaseUri() . ($this->getSpecifiedEndpoint() ?? '');
    }

    public function getSpecifiedEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function getBaseUri(): string
    {
        $uri = $this->isMapi() ? $this->getMapiBaseUri() : $this->getApiBaseUri();

        $this->resetForcedMapi();

        return $uri;
    }

    public function getApiBaseUri(): string
    {
        return static::$apiEndpoint . $this->getTeamProjectSegment();
    }

    public function getMapiBaseUri(): string
    {
        return static::$mapiEndpoint . $this->getTeamProjectSegment();
    }

    public function isMapi(): bool
    {
        return $this->forceMapi || ($this->mapi ?? false);
    }

    protected function resetForcedMapi(): void
    {
        $this->forceMapi = false;
    }

    protected function getTeamProjectSegment(): string
    {
        return trim(config('typedcms.base_uri'), '/') . '/';
    }
}
