<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Models\Resolvers;

use TypedCMS\LaravelStarterKit\Models\Construct;
use TypedCMS\LaravelStarterKit\Models\Model;
use TypedCMS\LaravelStarterKit\Models\Resolvers\BasicResolver;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Models\Foo;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Models\FooConstruct;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use UnexpectedValueException;
use function dirname;

class BasicResolverTest extends TestCase
{
    private BasicResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolver = new class extends BasicResolver {

            protected function getPath(): string
            {
                return dirname(__DIR__, 3) . '/Fixture/Models';
            }

            protected function getNamespace(): string
            {
                return 'TypedCMS\\LaravelStarterKit\\Tests\\Fixture\\Models';
            }
        };
    }

    /**
     * @test
     */
    public function itResolvesAModelByResourceType(): void
    {
        $model = $this->resolver->resolve('bars');

        $this->assertEquals($model->getType(), 'bars');
    }

    /**
     * @test
     */
    public function itResolvesNullByResourceTypeForModelsThatDontExist(): void
    {
        $this->assertNull($this->resolver->resolve('baz'));
    }

    /**
     * @test
     */
    public function itResolvesTheDefaultConstructModelForConstructs(): void
    {
        $model = $this->resolver->resolve('constructs');

        $this->assertInstanceOf(Construct::class, $model);
    }

    /**
     * @test
     */
    public function itResolvesTheDefaultConstructModelForGlobals(): void
    {
        /** @var Construct $model */
        $model = $this->resolver->resolve('globals');

        $this->assertInstanceOf(Construct::class, $model);
    }

    /**
     * @test
     */
    public function itResolvesASpecialisedConstructModelByResourceTypePath(): void
    {
        $model = $this->resolver->resolve('constructs:foo');

        $this->assertInstanceOf(FooConstruct::class, $model);
    }

    /**
     * @test
     */
    public function itResolvesAGenericConstructModelByResourceTypePath(): void
    {
        $model = $this->resolver->resolve('constructs:baz');

        $this->assertInstanceOf(Construct::class, $model);
    }

    /**
     * @test
     */
    public function itResolvesASpecialisedGlobalModelByResourceTypePath(): void
    {
        /** @var FooConstruct $model */
        $model = $this->resolver->resolve('globals:foo');

        $this->assertInstanceOf(FooConstruct::class, $model);

        $this->assertTrue($model->isGlobal());
    }

    /**
     * @test
     */
    public function itResolvesAGenericGlobalsModelByResourceTypePath(): void
    {
        /** @var Construct $model */
        $model = $this->resolver->resolve('globals:baz');

        $this->assertInstanceOf(Construct::class, $model);

        $this->assertTrue($model->isGlobal());
    }

    /**
     * @test
     */
    public function itSkipsInvalidModels(): void
    {
        $this->app->instance(Foo::class, new class {});

        $this->assertNull($this->resolver->resolve('foo'));
    }

    /**
     * @test
     */
    public function itResolvesWhenThePathDoesNotExist(): void
    {
        $resolver = new class extends BasicResolver {

            protected function getPath(): string
            {
                return dirname(__DIR__, 3) . '/Fixture/NotModels';
            }

            protected function getNamespace(): string
            {
                return 'TypedCMS\\LaravelStarterKit\\Tests\\Fixture\\Models';
            }
        };

        $model = $resolver->resolve('constructs:baz');

        $this->assertInstanceOf(Construct::class, $model);
    }
}
