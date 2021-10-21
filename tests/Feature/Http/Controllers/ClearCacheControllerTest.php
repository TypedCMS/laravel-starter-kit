<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Feature\Http\Controllers;

use Illuminate\Support\Str;
use Mockery\MockInterface;
use TypedCMS\LaravelStarterKit\Repositories\GlobalsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Repository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\BarBazConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\BarConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooBarConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\NonCacheableGlobalsRepository;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use function realpath;

class ClearCacheControllerTest extends TestCase
{
    private string $webhookSecret;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookSecret = $this->getSecret();

        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set(
            'typedcms.repositories.resolver_path',
            realpath(__DIR__ . '/../../../Fixture/Repositories')
        );

        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set(
            'typedcms.repositories.resolver_namespace',
            'TypedCMS\\LaravelStarterKit\\Tests\\Fixture\\Repositories'
        );

        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('typedcms.webhook_secrets.cache', $this->webhookSecret);
    }

    /**
     * @test
     */
    public function itAcceptsAValidRequestSignature(): void
    {
        $payload = [
            'domain' => 'blueprints',
            'event' => 'create',
            'blueprint' => [
                'id' => 123,
                'identifier' => 'foo',
            ]
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function itRejectsAInvalidRequestSignature(): void
    {
        $payload = [
            'domain' => 'blueprints',
            'event' => 'create',
            'blueprint' => [
                'id' => 123,
                'type' => 'foo',
            ]
        ];

        $mitmPayload = $payload + ['sneaky' => 'sneaky'];

        $response = $this->postJson('/webhooks/clear-cache', $mitmPayload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'failure',
            'messages' => ['Invalid signing key.'],
        ]);
    }

    /**
     * @test
     */
    public function itTakesNoActionForNonClearableDomains(): void
    {
        $payload = [
            'domain' => 'foobar',
            'event' => 'create',
            //...
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['No action required.'],
        ]);
    }

    /**
     * @test
     */
    public function itTakesNoActionForNonClearableConstructsEvents(): void
    {
        $payload = [
            'domain' => 'constructs',
            'event' => 'foo',
            //...
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['No action required.'],
        ]);
    }

    /**
     * @test
     */
    public function itTakesNoActionForNonClearableGlobalsEvents(): void
    {
        $payload = [
            'domain' => 'globals',
            'event' => 'foo',
            //...
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['No action required.'],
        ]);
    }

    /**
     * @test
     */
    public function itTakesNoActionWhenNotACacheableGlobalRepository(): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('typedcms.globals_repo', NonCacheableGlobalsRepository::class);

        $payload = [
            'domain' => 'globals',
            'event' => 'update',
            //...
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['Custom globals repository is not cacheable. No action taken.'],
        ]);
    }

    /**
     * @test
     */
    public function itClearsACacheableGlobalRepository(): void
    {
        $this->app->instance(
            GlobalsRepository::class,
            $this->partialMock(GlobalsRepository::class, static function (MockInterface $mock) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('clearCache')->once();
            })
        );

        $payload = [
            'domain' => 'globals',
            'event' => 'update',
            //...
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['Globals Cache Cleared!'],
        ]);
    }

    /**
     * @test
     */
    public function isClearsCacheableConstructsRepositories(): void
    {
        $this->app->instance(FooConstructsRepository::class,
            $this->partialMock(FooConstructsRepository::class, static function (MockInterface $mock) {
                $mock->shouldNotReceive('clearCache');
            })
        );

        $this->app->instance(FooBarConstructsRepository::class,
            $this->partialMock(FooBarConstructsRepository::class, static function (MockInterface $mock) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('clearCache')->once();
            })
        );

        $this->app->instance(BarConstructsRepository::class,
            $this->partialMock(BarConstructsRepository::class, static function (MockInterface $mock) {
                $mock->shouldNotReceive('clearCache');
            })
        );

        $this->app->instance(BarBazConstructsRepository::class,
            $this->partialMock(BarBazConstructsRepository::class, static function (MockInterface $mock) {
                $mock->shouldNotReceive('clearCache');
            })
        );

        $payload = [
            'domain' => 'constructs',
            'event' => 'update',
            'blueprint' => [
                'identifier' => 'foo',
            ],
            'construct' => [
                'id' => 123,
            ]
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['Constructs Cache Cleared!'],
        ]);
    }

    /**
     * @test
     */
    public function isPropagatesClearingConstructs(): void
    {
        $this->app->instance(FooBarConstructsRepository::class,
            $this->partialMock(FooBarConstructsRepository::class, static function (MockInterface $mock) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('clearCache')->once();
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('getCacheClears')->andReturn([BarBazConstructsRepository::class])->once();
            })
        );

        $this->app->instance(BarBazConstructsRepository::class,
            $this->partialMock(BarBazConstructsRepository::class, static function (MockInterface $mock) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('clearCache')->once();
                /**
                 * @phpstan-ignore-next-line
                 */
                $mock->shouldReceive('getCacheClears')->andReturn([])->once();
            })
        );

        $payload = [
            'domain' => 'constructs',
            'event' => 'react',
            'blueprint' => [
                'identifier' => 'foo',
            ],
            'construct' => [
                'id' => 123,
            ]
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['Constructs Cache Cleared!'],
        ]);
    }

    /**
     * @test
     */
    public function itTakesNoActionWhenThereAreNoCacheableConstructsRepositories(): void
    {
        $payload = [
            'domain' => 'constructs',
            'event' => 'update',
            'blueprint' => [
                'identifier' => 'baz',
            ],
            'construct' => [
                'id' => 123,
            ]
        ];

        $response = $this->postJson('/webhooks/clear-cache', $payload, [
            'Signature' => $this->generateTestSigningKey($payload, $this->webhookSecret),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'messages' => ['No cacheable constructs repositories are configured. No action taken.'],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function generateTestSigningKey(array $payload, string $secret): string
    {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    private function getSecret(): string
    {
        return Str::random();
    }
}
