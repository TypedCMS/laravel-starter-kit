<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function dirname;
use function lcfirst;
use function sprintf;
use function str;
use function str_replace;
use function trim;

final class ScaffoldCommand extends GeneratorCommand
{
    protected $name = 'typedcms:scaffold';

    protected $description = 'Generates scaffolding classes for a blueprint';

    private string $building;

    public function handle(): bool
    {
        $this->makeModel();
        $this->makeRepository();
        $this->makeConstructController();
        $this->makeCollectionController();

        return true;
    }

    protected function getNameInput(): string
    {
        return trim($this->argument('blueprint'));
    }

    /**
     * @return array<array<int|string>>
     */
    protected function getArguments()
    {
        return [
            ['blueprint', InputArgument::REQUIRED, 'The scaffolding blueprint'],
        ];
    }

    /**
     * @return array<array<int|string|null>>
     */
    protected function getOptions(): array
    {
        return [
            ['collection', 'c', InputOption::VALUE_REQUIRED, 'Optionally specify the scaffolding collection'],
            ['force', null, InputOption::VALUE_NONE, 'Scaffold the classes even if they already exist'],
        ];
    }

    protected function getStub(): string
    {
        return match ($this->building) {
            default => $this->getStubPath('controller.construct.stub'),
            'controller.collection' => $this->getStubPath('controller.collection.stub'),
        };
    }

    protected function buildClass($name): string
    {
        return $this->replaceVariables(parent::buildClass($name));
    }

    protected function replaceVariables(string $stub): string
    {
        $repoName = $this->getCollectionClass().'Repository';
        $repoClass = trim($this->rootNamespace(), '\\').'\\Repositories\\'.$repoName;

        $replace = [
            'repository' => $repoName,
            'repositoryImport' => 'use '.$repoClass.';',
            'collectionVar' => lcfirst($this->getCollectionClass()),
            'collectionView' => $this->getViewDir().'.'.$this->getCollectionName(),
            'constructVar' => lcfirst($this->getModelClass()),
            'constructView' => $this->getViewDir().'.'.$this->getBlueprintName(),
        ];

        foreach ($replace as $variable => $value) {
            $stub = str_replace('{{ '.$variable.' }}', $value, $stub);
        }

        return $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Http\\Controllers';
    }

    protected function makeModel(): void
    {
        $this->call('typedcms:make:model', [
            'name' => $this->getModelClass(),
            '--blueprint' => $this->getBlueprintName(),
            '--force' => $this->option('force'),
        ]);
    }

    protected function makeRepository(): void
    {
        $this->call('typedcms:make:repository', [
            'name' => $this->getCollectionClass().'Repository',
            '--blueprint' => $this->getBlueprintName(),
            '--collection' => $this->getCollectionName(),
            '--force' => $this->option('force'),
        ]);
    }

    protected function makeConstructController(): void
    {
        $this->building = 'controller.construct';

        $name = $this->getControllerDir().'\\'.$this->getModelClass().'Controller';

        if (!$this->verifyClassName($name)) {
            return;
        }

        $this->generateClass($name, 'Construct controller');
    }

    protected function makeCollectionController(): void
    {
        $this->building = 'controller.collection';

        $name = $this->getControllerDir().'\\'.$this->getCollectionClass().'Controller';

        if (!$this->verifyClassName($name)) {
            return;
        }

        $this->generateClass($name, 'Collection controller');
    }

    protected function generateClass(string $name, string $type): void
    {
        $name = $this->qualifyClass($name);

        $path = $this->getPath($name);

        if (!$this->option('force') && $this->alreadyExists($name)) {
            $this->components->error($type.' already exists.');

            return;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->components->info(sprintf('Controller [%s] created successfully.', $path));
    }

    protected function verifyClassName(string $name): bool
    {
        if ($this->isReservedName($name)) {
            $this->components->error('The name "'.$name.'" is reserved by PHP.');

            return false;
        }

        return true;
    }

    protected function getBlueprintName(): string
    {
        return trim($this->argument('blueprint'));
    }

    protected function getCollectionName(): string
    {
        return trim(
            $this->option('collection') ??
            (string) str($this->getBlueprintName())->plural()->kebab(),
        );
    }

    protected function getModelClass(): string
    {
        return (string) str($this->getBlueprintName())->studly();
    }

    protected function getCollectionClass(): string
    {
        return (string) str($this->getCollectionName())->studly();
    }

    protected function getControllerDir(): string
    {
        return (string) str($this->getBlueprintName())->plural()->studly();
    }

    protected function getViewDir(): string
    {
        return (string) str($this->getBlueprintName())->plural()->kebab();
    }

    protected function getStubPath(string $stub): string
    {
        return dirname(__DIR__, 3).'/stubs/'.$stub;
    }
}
