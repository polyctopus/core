<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Models\ContentField;
use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\FieldTypes\TextFieldType;

class TestFactory
{
    public static function contentType(string $code = 'article'): ContentType
    {
        return new ContentType(
            id: $code,
            code: strtolower($code),
            label: ucfirst($code)
        );
    }

    public static function contentTypeWithTextField(string $code = 'article'): ContentType
    {
        return new ContentType(
            id: $code,
            code: strtolower($code),
            label: ucfirst($code),
            fields: [
                new ContentField(
                    id: 'f1',
                    contentTypeId: $code,
                    code: 'title',
                    label: 'Title',
                    fieldType: new TextFieldType(),
                    settings: ['maxLength' => 255]
                ),
                 new ContentField(
                    id: 'f1',
                    contentTypeId: $code,
                    code: 'contact',
                    label: 'Contact',
                    fieldType: new TextFieldType(),
                    settings: ['maxLength' => 255]
                )
            ]
        );
    }

    public static function content(ContentType $type, ContentStatus $contentStatus, array $data): Content
    {
        return new Content(
            id: uniqid('c_', true),
            contentType: $type,
            status: $contentStatus,
            data: $data
        );
    }
}