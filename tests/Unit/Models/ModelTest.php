<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Models;

use Carbon\Carbon;
use Swis\JsonApi\Client\Meta;
use TypedCMS\LaravelStarterKit\Tests\TestCase;
use TypedCMS\PHPStarterKit\Models\Model;

class ModelTest extends TestCase
{
    private Model $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = (new Model())
            ->setMeta(new Meta(['type' => 'foo']))
            ->setId('123')
            ->fill([
                'id' => '123',
                'created' => '1970-01-19T23:50:03.628+01:00',
                'updated' => '1970-01-19T23:50:03.628+01:00',
            ]);
    }

    /**
     * @test
     */
    public function itParsesCreatedAttributeToCarbon(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->model->created);
    }

    /**
     * @test
     */
    public function itParsesUpdatedAttributeToCarbon(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->model->updated);
    }
}
