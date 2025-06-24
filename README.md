# Polyctopus Core

Polyctopus Core is a lightweight PHP library for managing structured content and content types, inspired by headless CMS concepts. It provides a flexible, type-safe way to define content models, fields, and to manage content instances in your application.

## Features

- Define custom content types with fields and validation
- Create, update, find, and delete content entries
- In-memory repositories for rapid prototyping and testing
- Extensible field type system (e.g. Text fields, custom types)
- Service layer for business logic
- Type-safe content status using PHP enums (requires PHP 8.1+)
- Automatic validation of content data against field definitions
- **Versioning and rollback for content entries**  
  Every create and update operation creates a new version. You can rollback to any previous version at any time.

## Requirements

- PHP 8.3 or higher

## Installation

Install via Composer (assuming you have a `composer.json`):

```bash
composer require polyctopus/core
```

## Usage Example

```php
use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Models\ContentField;
use Polyctopus\Core\Models\FieldTypes\TextFieldType;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVersionRepository;
use Polyctopus\Core\Services\ContentService;

// Setup repositories
$contentRepo = new InMemoryContentRepository();
$contentTypeRepo = new InMemoryContentTypeRepository();
$contentVersionRepo = new InMemoryContentVersionRepository();

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
$service = new ContentService($contentRepo, $contentTypeRepo, $contentVersionRepo);

// Create content (status defaults to Draft, creates initial version)
$content = $service->create('c1', $articleType, ['title' => 'Hello World!']);

// Update content and set status (creates a new version)
$service->update($content, ContentStatus::Published, ['title' => 'Updated Title']);

// Further updates create more versions
$service->update($content, ContentStatus::Published, ['title' => 'Second Version']);

// List all content types
$contentTypes = $service->listContentTypes();

// Access content versions (example for in-memory repo)
$versions = $contentVersionRepo->findByEntity('content', 'c1');

// Rollback to a previous version (using the first version's ID)
$firstVersion = reset($versions);
if ($firstVersion) {
    $service->rollback('c1', $firstVersion->getId());
}

// Delete content
$service->delete('c1');
```

## Versioning & Rollback

- **Automatic versioning:**  
  Every time you create or update content using the service, a new version is created and stored in the `ContentVersionRepositoryInterface`.
- **Rollback:**  
  You can access all versions for a content entry and use the `rollback($contentId, $versionId)` method of the service to restore a previous state.

## Validation

When creating or updating content, the service automatically validates the data against the field definitions of the content type. If validation fails (e.g. a string is too long), an `InvalidArgumentException` is thrown.

## Extending

- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage.
- Add more content statuses by extending the `ContentStatus` enum.

## License

LGPL-3.0-or-later