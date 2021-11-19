<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Resolvers;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Repository;
use TypedCMS\LaravelStarterKit\Repositories\Resolvers\Contracts\ResolvesRepositories;
use UnexpectedValueException;
use function app;
use function app_path;
use function config;
use function file_exists;

class BasicResolver implements ResolvesRepositories
{
    /**
     * @var array<Repository>|null
     */
    protected ?array $repos = null;

    public function resolveByBlueprint(string $blueprint): array
    {
        $repos = [];

        foreach ($this->getRepositories() as $repo) {

            if (
                $repo instanceof ConstructsRepository &&
                $repo->getBlueprint() === $blueprint
            ) {
                $repos[] = $repo;
            }
        }

        return $repos;
    }

    public function resolveByEndpoint(string $endpoint): array
    {
        $repos = [];

        foreach ($this->getRepositories() as $repo) {

            if ($repo->getSpecifiedEndpoint() === $endpoint) {
                $repos[] = $repo;
            }
        }

        return $repos;
    }

    /**
     * @return array<Repository>
     */
    protected function getRepositories(): array
    {
        if ($this->repos === null) {

            $this->repos = [];
            $files = [];

            if (file_exists($this->getPath())) {
                $files = new RegexIterator(new DirectoryIterator($this->getPath()), '/\.php$/');
            }

            /** @var SplFileInfo $file */
            foreach ($files as $file) {

                /** @var object $repo */
                $repo = app($this->getNamespace() . '\\' . $file->getBasename('.php'));

                if (!$repo instanceof Repository) {
                    throw new UnexpectedValueException('Resolved repositories must be instances of ' . Repository::class);
                }

                $this->repos[] = $repo;
            }
        }

        return $this->repos;
    }

    protected function getPath(): string
    {
        return config('typedcms.repositories.resolver_path', app_path('Repositories'));
    }

    protected function getNamespace(): string
    {
        return config('typedcms.repositories.resolver_namespace', 'App\\Repositories');
    }
}
