<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories;

class ConstructsRepository extends Repository
{
    protected string $collection;

    protected string $blueprint;

    public function getBlueprint(): string
    {
        return $this->blueprint;
    }

    public function getCollection(): string
    {
        return $this->collection;
    }

    public function getEndpoint(): string
    {
        if ($this->isMapi()) {
            return "{$this->getBaseUri()}constructs/{$this->getBlueprint()}";
        }

        return $this->getBaseUri() . $this->getCollection();
    }

    final public function getSpecifiedEndpoint(): ?string
    {
        return null;
    }
}
