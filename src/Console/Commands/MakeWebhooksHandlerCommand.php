<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use function dirname;

class MakeWebhooksHandlerCommand extends GeneratorCommand
{
    protected $name = 'typedcms:make:webhooks-handler';

    protected $description = 'Generate a new webhooks handler class';

    protected $type = 'Handler';


    protected function getStub(): string
    {
        return $this->getStubPath('handler.webhook.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Http\\Controllers\\Webhooks\\Handlers';
    }

    protected function getStubPath(string $stub): string
    {
        return dirname(__DIR__, 3) . '/stubs/' . $stub;
    }
}
