<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

use function dirname;
use function str_replace;

final class MakeWebhooksControllerCommand extends GeneratorCommand
{
    protected $name = 'typedcms:make:webhooks-controller';

    protected $description = 'Generate a new webhooks controller class';

    protected $type = 'Controller';

    /**
     * @return array<array<int|string|null>>
     */
    protected function getOptions(): array
    {
        return [
            ['webhook', 'w', InputOption::VALUE_REQUIRED, 'Specify the generated controller\'s webhook name'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
        ];
    }

    protected function getStub(): string
    {
        return $this->getStubPath('controller.webhook.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Http\\Controllers\\Webhooks';
    }

    protected function getStubPath(string $stub): string
    {
        return dirname(__DIR__, 3).'/stubs/'.$stub;
    }

    protected function buildClass($name): string
    {
        return $this->replaceName(parent::buildClass($name));
    }

    protected function replaceName(string $stub): string
    {
        return str_replace('{{ webhook }}', $this->option('webhook') ?? '', $stub);
    }
}
