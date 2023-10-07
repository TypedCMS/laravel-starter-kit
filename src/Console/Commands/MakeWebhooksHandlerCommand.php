<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

use function dirname;

class MakeWebhooksHandlerCommand extends GeneratorCommand
{
    protected $name = 'typedcms:make:webhooks-handler';

    protected $description = 'Generate a new webhooks handler class';

    protected $type = 'Handler';

    /**
     * @return array<array<int|string>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the handler already exists'],
        ];
    }

    protected function getStub(): string
    {
        return $this->getStubPath('handler.webhook.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Webhooks\\Handlers';
    }

    protected function getStubPath(string $stub): string
    {
        return dirname(__DIR__, 3) . '/stubs/' . $stub;
    }
}
