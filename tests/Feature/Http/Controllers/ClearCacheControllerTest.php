<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Feature\Http\Controllers;

use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider;
use TypedCMS\LaravelStarterKit\Repositories\GlobalsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\BarBazConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\BarConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooBarConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories\FooConstructsRepository;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes\NonCacheableGlobalsRepository;

use function realpath;

final class ClearCacheControllerTest extends TestCase
{
    private string $webhookSecret;

    public function defineEnvironment($app): void
    {
        $this->webhookSecret = $this->getSecret();

        $app['config']->set(
            'typedcms.repositories.resolver_path',
            realpath(__DIR__ . '/../../../Fixture/Repositories')
        );

        $app['config']->set(
            'typedcms.repositories.resolver_namespace',
            'TypedCMS\\LaravelStarterKit\\Tests\\Fixture\\Repositories'
        );

        $app['config']->set('typedcms.webhook_secrets.cache', $this->webhookSecret);

        StarterKitServiceProvider::configurePHPStarterKit();
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function itTakesNoActionWhenNotACacheableGlobalRepository(): void
    {
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

    #[Test]
    public function itClearsACacheableGlobalRepository(): void
    {
        $this->app->instance(
            GlobalsRepository::class,
            $this->partialMock(GlobalsRepository::class, static function (MockInterface $mock) {
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

    #[Test]
    public function isClearsCacheableConstructsRepositories(): void
    {
        $this->app->instance(FooConstructsRepository::class,
            $this->partialMock(FooConstructsRepository::class, static function (MockInterface $mock) {
                $mock->shouldNotReceive('clearCache');
            })
        );

        $this->app->instance(FooBarConstructsRepository::class,
            $this->partialMock(FooBarConstructsRepository::class, static function (MockInterface $mock) {
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
        ]);
    }

    #[Test]
    public function isPropagatesClearingConstructs(): void
    {
        $this->app->instance(FooBarConstructsRepository::class,
            $this->partialMock(FooBarConstructsRepository::class, static function (MockInterface $mock) {

                $mock->shouldReceive('clearCache')->once();

                $mock->shouldReceive('getCacheClears')->andReturn([BarBazConstructsRepository::class])->once();
            })
        );

        $this->app->instance(BarBazConstructsRepository::class,
            $this->partialMock(BarBazConstructsRepository::class, static function (MockInterface $mock) {

                $mock->shouldReceive('clearCache')->once();

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
        ]);
    }

    #[Test]
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
            'messages' => ['No cacheable construct repositories are configured. No action taken.'],
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
