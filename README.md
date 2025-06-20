# Polysync Core

Polysync Core is a lightweight PHP library for managing structured content and content types, inspired by headless CMS concepts. It provides a flexible, type-safe way to define content models, fields, and to manage content instances in your application.

## Features

- Define custom content types with fields and validation
- Create, update, find, and delete content entries
- In-memory repositories for rapid prototyping and testing
- Extensible field type system (e.g. Text fields, custom types)
- Service layer for business logic

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

// Create content
$content = $service->create('c1', $articleType, ['title' => 'Hello World!']);

// Update content
$service->update($content, ['title' => 'Updated Title']);

// List all content types
$contentTypes = $service->listContentTypes();

// Delete content
$service->delete('c1');
```

## Extending

- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage.