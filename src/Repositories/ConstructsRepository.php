<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories;

use Illuminate\Support\Collection;
use TypedCMS\LaravelStarterKit\Models\Construct;

use function collect;

class ConstructsRepository extends Repository
{
    protected string $collection;

    protected string $blueprint;

    /**
     * @param array<string, mixed> $parameters
     */
    public function hierarchy(string $identifier, array $parameters = []): Collection
    {
        $document = $this->all(['hierarchy' => $identifier] + $parameters);

        /** @var Collection<int, Construct> $constructs */
        $constructs = $document->getData();

        return $this->traverseHierarchy($document->getMeta()['hierarchy']->tree, $constructs);
    }

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

    /**
     * @param array<mixed> $tree
     * @param Collection<int, Construct> $constructs
     *
     * @return Collection<mixed>
     */
    protected function traverseHierarchy(array $tree, Collection $constructs): Collection
    {
        return collect($tree)->map(fn (object $item) => (object) [
            'construct' => $constructs->firstWhere('identifier', $item->construct),
            'children' => $this->traverseHierarchy($item->children, $constructs),
        ]);
    }
}
