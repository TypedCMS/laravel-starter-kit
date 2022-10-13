<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit;

use Swis\JsonApi\Client\Parsers\DocumentParser as BaseDocumentParser;
use Swis\JsonApi\Client\Parsers\ErrorCollectionParser;
use Swis\JsonApi\Client\Parsers\JsonapiParser;
use Swis\JsonApi\Client\Parsers\LinksParser;
use Swis\JsonApi\Client\Parsers\MetaParser;

class DocumentParser extends BaseDocumentParser
{
    public function __construct(
        ItemParser $itemParser,
        CollectionParser $collectionParser,
        ErrorCollectionParser $errorCollectionParser,
        LinksParser $linksParser,
        JsonapiParser $jsonapiParser,
        MetaParser $metaParser,
    ) {
        parent::__construct(
            $itemParser,
            $collectionParser,
            $errorCollectionParser,
            $linksParser,
            $jsonapiParser,
            $metaParser,
        );
    }
}

