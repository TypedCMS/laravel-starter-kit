<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Models\Resolvers;

use DirectoryIterator;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Swis\JsonApi\Client\Interfaces\ItemInterface;
use TypedCMS\LaravelStarterKit\Models\Construct;
use TypedCMS\LaravelStarterKit\Models\Resolvers\Contracts\ResolvesModels;
use UnexpectedValueException;
use function app;
use function app_path;
use function config;
use function file_exists;
use function str_replace;

class BasicResolver implements ResolvesModels
{
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
        $models = [];
        $files = [];

        if (file_exists($this->getPath())) {
            $files = new RegexIterator(new DirectoryIterator($this->getPath()), '/\.php$/');
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {

            /** @var object $model */
            $model = app($this->getNamespace() . '\\' . $file->getBasename('.php'));

            if (!$model instanceof ItemInterface) {
                throw new UnexpectedValueException('Resolved models must be instances of ' . ItemInterface::class);
            }

            $models[] = $model;
        }

        return $models;
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
