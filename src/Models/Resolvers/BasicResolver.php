<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Models\Resolvers;

use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Swis\JsonApi\Client\Interfaces\ItemInterface;
use TypedCMS\LaravelStarterKit\Models\Construct;
use TypedCMS\LaravelStarterKit\Models\Resolvers\Contracts\ResolvesModels;
use UnexpectedValueException;

class BasicResolver implements ResolvesModels
{
    /**
     * @var array<ItemInterface>|null
     */
    protected ?array $models = null;

    public function resolve(string $type): ?ItemInterface
    {
        if ($type === 'constructs' || $type === 'globals') {
            return new Construct();
        }

        if (Str::startsWith($type, 'constructs:')) {
            return $this->resolveByConstructsPath(str_replace('constructs:', '', $type));
        }

        return $this->resolveByType($type);
    }

    public function resolveByConstructsPath(string $blueprint): Construct
    {

        foreach ($this->getModels() as $model) {

            if (
                $model instanceof Construct &&
                $model->getBlueprint() === $blueprint
            ) {
                return $model;
            }
        }

        return new Construct();
    }

    public function resolveByType(string $type): ?ItemInterface
    {
        foreach ($this->getModels() as $model) {

            if ($model->getType() === $type) {
                return $model;
            }
        }

        return null;
    }

    /**
     * @return array<ItemInterface>
     */
    protected function getModels(): array
    {
        if ($this->models === null) {

            $this->models = [];

            $files = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($this->getPath())
                ),
                '/\.php$/'
            );

            /** @var SplFileInfo $file */
            foreach ($files as $file) {

                /** @var object $model */
                $model = app($this->getNamespace() . '\\' . $file->getBasename('.php'));

                if (!$model instanceof ItemInterface) {
                    throw new UnexpectedValueException('Resolved models must be instances of ' . ItemInterface::class);
                }

                $this->models[] = $model;
            }
        }

        return $this->models;
    }

    protected function getPath(): string
    {
        return config('typedcms.models.resolver_path', app_path('Models'));
    }

    protected function getNamespace(): string
    {
        return config('typedcms.models.resolver_namespace', 'App\\Models');
    }
}
