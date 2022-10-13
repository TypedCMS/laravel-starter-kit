<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit;

use Swis\JsonApi\Client\Parsers\CollectionParser as BaseCollectionParser;

class CollectionParser extends BaseCollectionParser
{
    public function __construct(ItemParser $itemParser)
    {
        parent::__construct($itemParser);
    }
}

