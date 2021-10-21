<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Models;

use Swis\JsonApi\Client\Item;

class Foo extends Item
{
    protected $type = 'foos';
}

