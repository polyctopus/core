# Polysync Core

Polysync Core is a lightweight PHP library for managing structured content and content types, inspired by headless CMS concepts. It provides a flexible, type-safe way to define content models, fields, and to manage content instances in your application.

## Features

- Define custom content types with fields and validation
- Create, update, find, and delete content entries
- In-memory repositories for rapid prototyping and testing
- Extensible field type system (e.g. Text fields, custom types)
- Service layer for business logic
- Type-safe content status using PHP enums (requires PHP 8.1+)
- Automatic validation of content data against field definitions

## Requirements

- PHP 8.3 or higher

## Installation

Install via Composer (assuming you have a `composer.json`):

```bash
composer require polysync/core
```

## Usage Example

```php
use Polysync\Core\Models\ContentType;
use Polysync\Core\Models\ContentField;
use Polysync\Core\Models\FieldTypes\TextFieldType;
use Polysync\Core\Models\ContentStatus;
use Polysync\Core\Repositories\InMemory\InMemoryContentRepository;
use Polysync\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polysync\Core\Services\ContentService;

// Setup repositories
$contentRepo = new InMemoryContentRepository();
$contentTypeRepo = new InMemoryContentTypeRepository();

// Define a content type
$titleField = new ContentField(
    id: 'f1',
    contentTypeId: 'article',
    code: 'title',
    label: 'Title',
    fieldType: new TextFieldType(),
    settings: ['maxLength' => 255]
);

$articleType = new ContentType(
    id: 'article',
    code: 'article',
    label: 'Article',
    fields: [$titleField]
);

$contentTypeRepo->save($articleType);

// Use the ContentService
$service = new ContentService($contentRepo, $contentTypeRepo);

// Create content (status defaults to Draft)
$content = $service->create('c1', $articleType, ['title' => 'Hello World!']);

// Update content and set status
$service->update($content, ContentStatus::Published, ['title' => 'Updated Title']);

// List all content types
$contentTypes = $service->listContentTypes();

// Delete content
$service->delete('c1');
```

## Validation

When creating or updating content, the service automatically validates the data against the field definitions of the content type. If validation fails (e.g. a string is too long), an `InvalidArgumentException` is thrown.

## Extending

- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage.
- Add more content statuses by extending the `ContentStatus` enum.

## License

LGPL-3.0-or-later