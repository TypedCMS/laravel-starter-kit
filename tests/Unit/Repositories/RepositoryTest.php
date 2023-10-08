<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Swis\JsonApi\Client\Collection;
use Swis\JsonApi\Client\Document;
use Swis\JsonApi\Client\DocumentFactory;
use Swis\JsonApi\Client\Interfaces\DocumentClientInterface;
use Swis\JsonApi\Client\Interfaces\DocumentInterface;
use Swis\JsonApi\Client\Item;
use Swis\JsonApi\Client\Meta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\CacheableRepository;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\NonCacheableRepository;
use TypedCMS\PHPStarterKit\Repositories\Concerns\DeterminesEndpoint;

class RepositoryTest extends TestCase
{
    private string $apiEndpoint;

    private string $mapiEndpoint;

    public function defineEnvironment($app): void
    {
        $this->apiEndpoint = DeterminesEndpoint::$apiEndpoint;
        $this->mapiEndpoint = DeterminesEndpoint::$mapiEndpoint;

        $app['config']->set('typedcms.base_uri', '@foo/bar');

        StarterKitServiceProvider::configurePHPStarterKit();
    }

    /**
     * @test
     */
    public function itUsesTheSpecifiedEndpoint(): void
    {
        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame('things', $repository->getSpecifiedEndpoint());
    }

    /**
     * @test
     */
    public function itUsesApiEndpoints(): void
    {
        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame($this->getApiEndpoint('things'), $repository->getEndpoint());
        $this->assertSame('things', $repository->getSpecifiedEndpoint());
    }

    /**
     * @test
     */
    public function itUsesMapiEndpoints(): void
    {
        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame($this->getMapiEndpoint('things'), $repository->mapi()->getEndpoint());
    }

    /**
     * @test
     */
    public function itGetsAll(): void
    {
        $document = new Document();

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things?foo=bar&all=1'))
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->all(['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function itGetsPaginated(): void
    {
        $item = (new Item(['foo' => 'bar']))->setId('123');
        $collection = (new Collection([$item]));

        $meta = new Meta(['total' => 1, 'perPage' => 15]);
        $document = (new DocumentFactory())->make($collection)->setMeta($meta);

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things?foo=bar&all=0&page%5Bnumber%5D=1'))
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $paginator = new LengthAwarePaginator($collection, 1, 15, 1, ['path' => request()->url()]);

        $this->assertEquals($paginator, $repository->paginated(['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function itTakesOne(): void
    {
        $document = new Document();

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things?foo=bar'))
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->take(['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function itFindsOne(): void
    {
        $document = new Document();

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things/foo?bar=baz'))
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->find('foo', ['bar' => 'baz']));
    }

    /**
     * @test
     */
    public function itCanFindOneWithFindOrFail(): void
    {
        $document = new Document();

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things/foo?bar=baz'))
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->assertSame($document, $repository->findOrFail('foo', ['bar' => 'baz']));
    }

    /**
     * @test
     */
    public function itCanFailWithFindOrFail(): void
    {
        $response = $this->mock(ResponseInterface::class,
            static function (MockInterface $mock) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('getStatusCode')->andReturn(404)->once();
            }
        );

        $document = $this->mock(DocumentInterface::class,
            static function (MockInterface $mock) use ($response) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('hasErrors')->andReturn(true)->once();
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('getResponse')->andReturn($response)->once();
            }
        );

        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class,
            function (MockInterface $mock) use ($document) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('get')
                    ->with($this->getApiEndpoint('things/foo?bar=baz'))
                    ->andReturn($document)
                    ->once();
            }
        );

        $repository = new NonCacheableRepository($client, new DocumentFactory());

        $this->expectException(NotFoundHttpException::class);

        $repository->findOrFail('foo', ['bar' => 'baz']);
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    private function getMapiEndpoint(string $append): string
    {
        return "{$this->mapiEndpoint}@foo/bar/{$append}";
    }

    private function mockCacheRetrievalMethodCalls(MockInterface $mock, string $key, Document $document): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $mock->shouldReceive('tags')
            ->with([CacheableRepository::class])
            ->andReturnSelf()
            ->once();

        /**
         * @phpstan-ignore-next-line
         */
        $mock->shouldReceive('has')
            ->with($key)
            ->andReturnTrue()
            ->once();

        /**
         * @phpstan-ignore-next-line
         */
        $mock->shouldReceive('get')
            ->with($key)
            ->andReturn($document)
            ->once();
    }

    private function mockCacheStorageMethodCalls(MockInterface $mock, string $key, Document $document): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $mock->shouldReceive('tags')
            ->with([CacheableRepository::class])
            ->andReturnSelf()
            ->once();

        /**
         * @phpstan-ignore-next-line
         */
        $mock->shouldReceive('has')
            ->with($key)
            ->andReturnFalse()
            ->once();

        /**
         * @phpstan-ignore-next-line
         */
        $mock->shouldReceive('remember')
            ->withSomeOfArgs($key)
            ->andReturn($document)
            ->once();
    }

    private function registerMockCacheManager(CacheRepository $cache): void
    {
        $this->app->instance('cache',
            $this->mock(CacheManager::class, static function (MockInterface $mock) use ($cache) {
                /**
                 * @phpstan-ignore-next-line
                 */
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
        return ($mapi ? 'mapi:' : '') . CacheableRepository::class . ':' . $key . ':' . serialize($parameters);
    }
}
