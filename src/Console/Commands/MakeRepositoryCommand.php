<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

use function dirname;
use function str_replace;

class MakeRepositoryCommand extends GeneratorCommand
{
    protected $name = 'typedcms:make:repository';

    protected $description = 'Generate a new repository class';

    protected $type = 'Repository';

    /**
     * @return array<array<int|string>>
     */
    protected function getOptions(): array
    {
        return [
            ['endpoint', 'e', InputOption::VALUE_REQUIRED, 'Specify the generated repository\'s endpoint'],
            ['collection', 'c', InputOption::VALUE_REQUIRED, 'Specify the generated repository\'s collection'],
            ['blueprint', 'b', InputOption::VALUE_REQUIRED, 'Specify the generated repository\'s blueprint'],
            ['not-cacheable', 'N', InputOption::VALUE_NONE, 'Indicated the generated repository should not be cacheable'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the repository already exists'],
        ];
    }

    protected function getStub(): string
    {
        return
            $this->option('collection') || $this->option('blueprint') ?
                $this->getStubPath('repository.construct.stub') :
                $this->getStubPath('repository.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Repositories';
    }

    protected function getStubPath(string $stub): string
    {
        return dirname(__DIR__, 3) . '/stubs/' . $stub;
    }

    protected function buildClass($name): string
    {
        $class = $this->replaceCacheable(parent::buildClass($name));

        return
            $this->option('collection') || $this->option('blueprint') ?
                $this->replaceBlueprint($this->replaceCollection($class)) :
                $this->replaceEndpoint($class);
    }

    protected function replaceCacheable(string $stub): string
    {
        return str_replace(
            '{{ cacheable }}',
            $this->option('not-cacheable') ? '' : 'implements Cacheable',
            $stub
        );
    }

    protected function replaceEndpoint(string $stub): string
    {
        return str_replace('{{ endpoint }}', $this->option('endpoint') ?? '', $stub);
    }

    protected function replaceCollection(string $stub): string
    {
        return str_replace('{{ collection }}', $this->option('collection') ?? '', $stub);
    }

    protected function replaceBlueprint(string $stub): string
    {
        return str_replace('{{ blueprint }}', $this->option('blueprint') ?? '', $stub);
    }
}
