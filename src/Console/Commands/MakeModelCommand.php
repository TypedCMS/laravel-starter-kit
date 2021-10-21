<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use function dirname;
use function str_replace;

class MakeModelCommand extends GeneratorCommand
{
    protected $name = 'typedcms:make:model';

    protected $description = 'Generate a new model class';

    protected $type = 'Model';

    /**
     * @return array<array<int|string>>
     */
    protected function getOptions(): array
    {
        return [
            ['type', 't', InputOption::VALUE_REQUIRED, 'Specify the generated model\'s resource type'],
            ['blueprint', 'b', InputOption::VALUE_REQUIRED, 'Specify the generated model\'s blueprint'],
        ];
    }

    protected function getStub(): string
    {
        return
            $this->option('blueprint') ?
                $this->getStubPath('model.construct.stub') :
                $this->getStubPath('model.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Models';
    }

    protected function getStubPath(string $stub): string
    {
        return dirname(__DIR__, 3) . '/stubs/' . $stub;
    }

    protected function buildClass($name): string
    {
        return
            $this->option('blueprint') ?
                $this->replaceBlueprint(parent::buildClass($name)) :
                $this->replaceType(parent::buildClass($name));
    }

    protected function replaceType(string $stub): string
    {
        return str_replace('{{ type }}', $this->option('type') ?? '', $stub);
    }

    protected function replaceBlueprint(string $stub): string
    {
        return str_replace('{{ blueprint }}', $this->option('blueprint') ?? '', $stub);
    }
}
