<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Webhooks\Handlers\Cache;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooALoopingConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooBLoopingConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooCLoopingConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\ConstructsHandler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;
use TypedCMS\PHPStarterKit\Repositories\Resolvers\Contracts\ResolvesRepositories;

final class ConstructsHandlerTest extends TestCase
{
    #[Test]
    public function itClearsLoopingReposOnce(): void
    {
        /** @var ResolvesRepositories $resolver */
        $resolver = $this->mock(ResolvesRepositories::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('resolveByBlueprint')
                    ->with('foo-loop')
                    ->andReturn([
                        app(FooALoopingConstructsRepository::class),
                        app(FooBLoopingConstructsRepository::class),
                        app(FooCLoopingConstructsRepository::class),
                    ]);
            }
        );

        $handler = new ConstructsHandler($resolver);

        $handler->handle(new Traveler([
            'event' => 'update',
            'domain' => 'constructs',
            'project' => [
                'name' => 'Website',
                'identifier' => 'website',
            ],
            'blueprint' => [
                'id' => 4321,
                'name' => 'Foo',
                'identifier' => 'foo-loop',
            ],
            'construct' => [
                'id' => 1234,
            ],
        ]), fn () => function () {});

        $this->assertTrue(true);
    }
}
