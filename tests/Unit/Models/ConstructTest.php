<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Models;

use Swis\JsonApi\Client\Meta;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Models\FooConstruct;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\PHPStarterKit\Models\Construct;
use TypedCMS\PHPStarterKit\Models\Resolvers\BasicResolver;
use TypedCMS\PHPStarterKit\Models\Resolvers\Contracts\ResolvesModels;
use UnexpectedValueException;

class ConstructTest extends TestCase
{
    private Construct $model;

    public function defineEnvironment($app): void
    {
        $app->instance(ResolvesModels::class, new class extends BasicResolver {

            protected function getPath(): string
            {
                return dirname(__DIR__, 2) . '/Fixture/Models';
            }

            protected function getNamespace(): string
            {
                return 'TypedCMS\\LaravelStarterKit\\Tests\\Fixture\\Models';
            }
        });

        $this->model = new Construct();
        $this->model->setMeta(new Meta(['type' => 'foo']));
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenTypeNotInMeta(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->model->setMeta(new Meta([]));
    }

    /**
     * @test
     */
    public function itDiscoversTheBlueprintInMeta(): void
    {
        $this->assertEquals($this->model->getBlueprint(), 'foo');
    }

    /**
     * @test
     */
    public function itResolvesASpecialisedConstructModel(): void
    {
        $model = $this->model->specialize();

        $this->assertInstanceOf(FooConstruct::class, $model);
    }

    /**
     * @test
     */
    public function itResolvesTheCurrentConstructModelWhenNoSpecialisedOneCanBeResolved(): void
    {
        $original = new Construct();
        $specialized = $original->specialize();

        $this->assertSame($original, $specialized);
    }
}
