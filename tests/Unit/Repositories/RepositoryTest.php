<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Swis\JsonApi\Client\Collection;
use Swis\JsonApi\Client\Document;
use Swis\JsonApi\Client\DocumentFactory;
use Swis\JsonApi\Client\Interfaces\DocumentClientInterface;
use Swis\JsonApi\Client\Item;
use Swis\JsonApi\Client\Meta;
use TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\CacheableRepository;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\NonCacheableRepository;
use TypedCMS\PHPStarterKit\Repositories\Concerns\DeterminesEndpoint;

final class RepositoryTest extends TestCase
{
    private string $apiEndpoint;

    public function defineEnvironment($app): void
    {
        $this->apiEndpoint = DeterminesEndpoint::$apiEndpoint;

        $app['config']->set('typedcms.base_uri', '@foo/bar');

        StarterKitServiceProvider::configurePHPStarterKit();
    }

    #[Test]
    public function itGetsPaginated(): void
    {
        $item = (new Item(['foo' => 'bar']))->setId('123');
        $collection = (new Collection([$item]));

        $meta = new Meta(['total' => 1, 'perPage' => 15]);
        $document = (new DocumentFactory())->make($collection)->setMeta($meta);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things?foo=bar&all=0&page%5Bnumber%5D=1'), [])
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $paginator = new LengthAwarePaginator($collection, 1, 15, 1, ['path' => request()->url()]);

        $this->assertEquals($paginator, $repository->paginated(['foo' => 'bar']));
    }

    #[Test]
    public function itGetsAllFromCache(): void
    {
        $document = new Document();
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $parameters) {

                $key = $this->makeCacheKey('all', $parameters + ['all' => true]);

                $this->mockCacheRetrievalMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            static function (MockInterface $mock) {
                $mock->shouldNotReceive('get');
            }
        );

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->all($parameters));
    }

    #[Test]
    public function itGetsPaginatedFromCache(): void
    {
        $item = (new Item(['foo' => 'bar']))->setId('123');
        $collection = (new Collection([$item]));

        $meta = new Meta(['total' => 1, 'perPage' => 15]);
        $document = (new DocumentFactory())->make($collection)->setMeta($meta);

        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $parameters) {

                $key = $this->makeCacheKey('paginated', $parameters + ['all' => false, 'page[number]' => 1]);

                $this->mockCacheRetrievalMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            static function (MockInterface $mock) {
                $mock->shouldNotReceive('get');
            }
        );

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertInstanceOf(LengthAwarePaginator::class, $repository->paginated($parameters));
    }

    #[Test]
    public function itTakesOneFromCache(): void
    {
        $document = new Document();
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $parameters) {

                $key = $this->makeCacheKey('take', $parameters);

                $this->mockCacheRetrievalMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            static function (MockInterface $mock) {
                $mock->shouldNotReceive('get');
            }
        );

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->take($parameters));
    }

    #[Test]
    public function itFindsOneFromCache(): void
    {
        $document = new Document();

        $id = '123';
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $id, $parameters) {

                $key = $this->makeCacheKey('find:'.$id, $parameters);

                $this->mockCacheRetrievalMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            static function (MockInterface $mock) {
                $mock->shouldNotReceive('get');
            }
        );

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->find($id, $parameters));
    }

    #[Test]
    public function itFindsOneNotFailedFromCache(): void
    {
        $document = new Document();

        $id = '123';
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $id, $parameters) {

                $key = $this->makeCacheKey('findOrFail:'.$id, $parameters);

                $this->mockCacheRetrievalMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            static function (MockInterface $mock) {
                $mock->shouldNotReceive('get');
            }
        );

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->findOrFail($id, $parameters));
    }

    #[Test]
    public function itPutsAllInCache(): void
    {
        $document = new Document();
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $parameters) {

                $key = $this->makeCacheKey('all', $parameters + ['all' => true]);

                $this->mockCacheStorageMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->all($parameters));
    }

    #[Test]
    public function itPutsPaginatedInCache(): void
    {
        $item = (new Item(['foo' => 'bar']))->setId('123');
        $collection = (new Collection([$item]));

        $meta = new Meta(['total' => 1, 'perPage' => 15]);
        $document = (new DocumentFactory())->make($collection)->setMeta($meta);

        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $parameters) {

                $key = $this->makeCacheKey('paginated', $parameters + ['all' => false, 'page[number]' => 1]);

                $this->mockCacheStorageMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertInstanceOf(LengthAwarePaginator::class, $repository->paginated($parameters));
    }

    #[Test]
    public function itPutsTakenInCache(): void
    {
        $document = new Document();
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $parameters) {

                $key = $this->makeCacheKey('take', $parameters);

                $this->mockCacheStorageMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->take($parameters));
    }

    #[Test]
    public function itPutsFoundInCache(): void
    {
        $document = new Document();

        $id = '123';
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $id, $parameters) {

                $key = $this->makeCacheKey('find:' . $id, $parameters);

                $this->mockCacheStorageMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->find($id, $parameters));
    }

    #[Test]
    public function itPutsFoundNotFailedInCache(): void
    {
        $document = new Document();

        $id = '123';
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $id, $parameters) {

                $key = $this->makeCacheKey('findOrFail:' . $id, $parameters);

                $this->mockCacheStorageMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->findOrFail($id, $parameters));
    }

    #[Test]
    public function itPutsFoundOnMapiInCache(): void
    {
        $document = new Document();

        $id = '123';
        $parameters = ['foo' => 'bar'];

        /** @var CacheRepository $cache */
        $cache = $this->mock(CacheRepository::class,
            function (MockInterface $mock) use ($document, $id, $parameters) {

                $key = $this->makeCacheKey('find:' . $id, $parameters, true);

                $this->mockCacheStorageMethodCalls($mock, $key, $document);
            }
        );

        $this->registerMockCacheManager($cache);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new CacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->mapi()->find($id, $parameters));
    }

    private function getApiEndpoint(string $append): string
    {
        return "{$this->apiEndpoint}@foo/bar/{$append}";
    }

    private function mockCacheRetrievalMethodCalls(MockInterface $mock, string $key, Document $document): void
    {
        $mock->shouldReceive('tags')
            ->with([CacheableRepository::class])
            ->andReturnSelf()
            ->once();

        $mock->shouldReceive('has')
            ->with($key)
            ->andReturnTrue()
            ->once();

        $mock->shouldReceive('get')
            ->with($key)
            ->andReturn($document)
            ->once();
    }

    private function mockCacheStorageMethodCalls(MockInterface $mock, string $key, Document $document): void
    {
        $mock->shouldReceive('tags')
            ->with([CacheableRepository::class])
            ->andReturnSelf()
            ->once();

        $mock->shouldReceive('has')
            ->with($key)
            ->andReturnFalse()
            ->once();

        $mock->shouldReceive('remember')
            ->withSomeOfArgs($key)
            ->andReturn($document)
            ->once();
    }

    private function registerMockCacheManager(CacheRepository $cache): void
    {
        $this->app->instance('cache',
            $this->mock(CacheManager::class, static function (MockInterface $mock) use ($cache) {
                $mock->shouldReceive('driver')
                    ->with(null)
                    ->andReturn($cache)
                    ->once();
            })
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function makeCacheKey(string $key, array $parameters, bool $mapi = false): string
    {
        return ($mapi ? 'mapi:' : '').CacheableRepository::class.':'.$key.':'.serialize($parameters);
    }
}
