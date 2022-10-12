<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit;

use Swis\JsonApi\Client\Interfaces\DocumentInterface;
use Swis\JsonApi\Client\Parsers\DocumentParser as BaseDocumentParser;

use function is_array;
use function is_object;
use function json_decode;
use function json_encode;
use function property_exists;

class DocumentParser extends BaseDocumentParser
{
    public function parse(string $json): DocumentInterface
    {
        $data = json_decode($json, false);

        if (is_object($data) && property_exists($data, 'data')) {

            if (is_object($data->data)) {
                $data->data = $this->parseOne($data->data);
            }

            if (is_array($data->data)) {
                $data->data = $this->parseMany($data->data);
            }
        }

        return parent::parse(json_encode($data));
    }

    /**
     * @param array<object> $documents
     *
     * @return array<object>
     */
    private function parseMany(array $documents): array
    {
        $parsed = [];

        foreach ($documents as $document) {
            $parsed[] = $this->parseOne($document);
        }

        return $parsed;
    }

    private function parseOne(object $document): object
    {
        if (
            property_exists($document, 'type') &&
            $document->type === 'constructs' &&
            property_exists($document, 'meta') &&
            property_exists($document->meta, 'type')
        ) {
            $document->type = 'constructs:' . $document->meta->type;
        }

        return $document;
    }
}

