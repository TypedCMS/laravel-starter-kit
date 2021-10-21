<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories;

use Swis\JsonApi\Client\DocumentFactory;
use Swis\JsonApi\Client\Interfaces\DocumentClientInterface;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\NonCacheableGlobalsRepository;

class GlobalsRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function itUsesTheGlobalsEndpoint(): void
    {
        /** @var DocumentClientInterface $client */
        $client = $this->mock(DocumentClientInterface::class);

        $repository = new NonCacheableGlobalsRepository($client, new DocumentFactory());

        $this->assertSame('globals', $repository->getSpecifiedEndpoint());
    }
}
