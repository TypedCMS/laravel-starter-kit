<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Models;

use Carbon\Carbon;
use Swis\JsonApi\Client\Item;

/**
 * @property string $id
 * @property Carbon $created
 * @property Carbon $updated
 */
class Model extends Item
{
    public function getCreatedAttribute(): Carbon
    {
        return Carbon::parse($this->attributes['created']);
    }

    public function getUpdatedAttribute(): Carbon
    {
        return Carbon::parse($this->attributes['updated']);
    }
}

