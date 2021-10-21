<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories;

use Swis\JsonApi\Client\DocumentFactory;
use Swis\JsonApi\Client\Interfaces\DocumentClientInterface;
use TypedCMS\LaravelStarterKit\Repositories\Concerns\DeterminesEndpoint;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\NonCacheableConstructsRepository;

class ConstructsRepositoryTest extends TestCase
{
    private string $apiEndpoint;

    private string $mapiEndpoint;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiEndpoint = DeterminesEndpoint::$apiEndpoint;
        $this->mapiEndpoint = DeterminesEndpoint::$mapiEndpoint;

        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('typedcms.base_uri', '@foo/bar');
    }

    /**
     * @test
     */
    public function itUsesApiEndpointsViaCollections(): void
    {
        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new NonCacheableConstructsRepository($client, new DocumentFactory());

        $this->assertSame($this->apiEndpoint . '@foo/bar/things', $repository->getEndpoint());
    }

    /**
     * @test
     */
    public function itUsesMapiEndpointsWithBlueprint(): void
    {
        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new NonCacheableConstructsRepository($client, new DocumentFactory());

        $this->assertSame($this->mapiEndpoint . '@foo/bar/constructs/thing', $repository->mapi()->getEndpoint());
    }
}
