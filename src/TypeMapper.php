<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit;

use Swis\JsonApi\Client\Interfaces\ItemInterface;
use Swis\JsonApi\Client\TypeMapper as BaseTypeMapper;
use TypedCMS\LaravelStarterKit\Models\Model;
use TypedCMS\LaravelStarterKit\Models\Resolvers\Contracts\ResolvesModels;

class TypeMapper extends BaseTypeMapper
{
    public function __construct(protected ResolvesModels $resolver) { }

    public function hasMapping(string $type): bool
    {
        return true;
    }

    public function getMapping(string $type): ItemInterface
    {
        return $this->resolver->resolve($type) ?? (new Model())->setType($type);
    }
}

