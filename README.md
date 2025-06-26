# Polyctopus Core

Polyctopus Core is a lightweight PHP library for managing structured content and content types, inspired by headless CMS concepts. It provides a flexible, type-safe way to define content models, fields, and to manage content instances in your application.

## Features

- **Versioning and rollback** for content entries
- **Content Dimensions**: Support for content variants based on arbitrary dimensions (e.g. brands, channels, campaigns)
- **Multilingual content and variants**: Optional translations for any content or variant using locales (e.g. `de_DE`)
- **Event mechanism** for custom application logic based on triggered events by accepting callbacks
- **Structured validation errors** for better error handling
- **Test utilities** for easy test data setup

## Requirements

- PHP 8.3 or higher

## Installation

Install via Composer (assuming you have a `composer.json`):

```bash
composer require polyctopus/core
```

## Usage Example

See the `example-usage.php` file in the repository for a complete example of how to use the library.

## Service Structure

Polyctopus Core uses a modular service structure for clear separation of concerns:

- `ContentService`: Core operations for content (create, update, delete, rollback, resolve, etc.)
- `ContentTypeService`: Manage content types (create, update, delete, list)
- `ContentVariantService`: Manage content variants for dimensions
- `ContentTranslationService`: Manage translations for content and variants
- `ContentVersionService`: Manage content versioning and rollback

You typically obtain all services pre-wired via the `InMemoryContentServiceFactory` or a similar factory.
The ContentService is the main entry point for all operations, and it uses the other services internally.

**Example:**
```php
use Polyctopus\Core\Services\InMemoryContentServiceFactory;

$service = InMemoryContentServiceFactory::create(); // returns a ContentService with all dependencies
$service->contentTypeService->createContentType(TestFactory::contentTypeWithTextField('article'));
```

## Content Types

Content types define the structure (fields, validation, etc.) for your content entries.  
You should always create and register a content type before creating content entries of that type.

```php
use Polyctopus\Core\Services\TestFactory;

// Create a ContentType (e.g. "Article" with a "title" and "contact" field)
$contentType = TestFactory::contentTypeWithTextField('article');
$service->contentTypeService->createContentType($contentType);
```

## Creating Content

```php
use Polyctopus\Core\Models\ContentStatus;

// Create a new Content (valid)
$content = $service->createContent('c1', $contentType, ['title' => 'Hello', 'contact' => 'info@example.com']);
echo "Created content: " . print_r($content->toArray(), true) . PHP_EOL;

// Update Content (valid and status change)
$service->updateContent($content, ContentStatus::Published, ['title' => 'Updated Title', 'contact' => 'info@example.com']);
$updated = $service->findContent('c1');
echo "Updated content: " . print_r($updated->toArray(), true) . PHP_EOL;
```

## Content Variants (Dimension Overrides)

Polyctopus Core supports content variants for arbitrary dimensions (e.g. brands, channels, campaigns).  
A variant allows you to override specific fields of a content entry for a given dimension, without duplicating the entire content.  
This is useful if, for example, you want to show a different title or description for a specific brand or channel, while all other fields remain as in the original content.

**How it works:**
- You create a `ContentVariant` for a given content ID and dimension, specifying only the fields you want to override.
- When resolving content for a dimension, the service merges the original content data with the overrides from the variant.
- If no variant exists for a dimension, the original content data is returned.

**Example:**
```php
use Polyctopus\Core\Models\ContentVariant;

// Create a variant for dimension "brand_a" that overrides the title
$variant = new ContentVariant(
    id: 'v1',
    contentId: 'c1',
    dimension: 'brand_a',
    overrides: ['title' => 'Brand A Title']
);
$service->contentVariantService->createContentVariant($variant);

// Resolve content for "brand_a"
$resolved = $service->resolveContentWithVariant('c1', 'brand_a');
// $resolved will contain the overridden title for brand_a

// Resolve content for a dimension without a variant
$resolvedDefault = $service->resolveContentWithVariant('c1', 'brand_b');
// $resolvedDefault will contain the original content data
```

## Multilingual Content & Translations

- [Translations](docs/Translations.md)

## Event Mechanism

Polyctopus Core provides a simple, framework-agnostic event mechanism.  
You can register event listeners (subscribers) for specific events and react to them in your application logic.  
For example, whenever new content is created, a `ContentCreated` event is dispatched.  
You can use this to trigger custom logic such as logging, notifications, or integrations.

**How it works:**
- Pass a callback (event dispatcher) to the `ContentService` constructor or use a dispatcher implementation.
- Register listeners for specific event types (e.g. `ContentCreated`).
- When an event occurs, all registered listeners for that event type are called with the event object.

**Example:**
```php
$service = InMemoryContentServiceFactory::create();

// Register a listener for content creation events
$service->setEventDispatcher(function($event) {
    if ($event instanceof \Polyctopus\Core\Events\ContentCreated) {
        echo "Content created: " . $event->getPayload()['content']->getId() . PHP_EOL;
    }
});
```
This mechanism is lightweight, easy to extend, and does not depend on any external framework.

## Versioning & Rollback

- **Automatic versioning:**  
  Every time you create or update content using the service, a new version is created and stored in the `ContentVersionRepositoryInterface`.
- **Rollback:**  
  You can access all versions for a content entry and use the `rollback($contentId, $versionId)` method of the service to restore a previous state.

**Example:**
```php
// List all versions for a content entry
$versions = $service->contentVersionService->findContentVersionsByEntityType('content', 'c1');
foreach ($versions as $version) {
    echo "- Version ID: {$version->getId()}, Snapshot: " . json_encode($version->toArray()['snapshot']) . PHP_EOL;
}

// Rollback to the first version
$firstVersion = reset($versions);
if ($firstVersion) {
    $service->rollback('c1', $firstVersion->getId());
}
```

## Validation

When creating or updating content, the service automatically validates the data against the field definitions of the content type.  
If validation fails (e.g. a string is too long), a `ValidationException` is thrown, containing structured error objects.

**Example:**
```php
try {
    $service->createContent('c2', $contentType, ['title' => str_repeat('A', 300)]);
} catch (\Polyctopus\Core\Exceptions\ValidationException $e) {
    foreach ($e->getErrors() as $error) {
        echo "Field: {$error->field}, Value: " . var_export($error->value, true) . ", Message: {$error->message}\n";
    }
}
```

## Asset Handling

Polyctopus Core provides a flexible way to manage and reference assets (such as images, documents, or other files) in your content.  
Assets are managed independently from content and can be linked to any content entry by storing their IDs in your content data.

### How it works

- **Asset Model:** An `Asset` represents a file with metadata (filename, MIME type, size, storage path, etc.).
- **Asset Repository Interface:** The `AssetRepositoryInterface` defines how assets are stored, retrieved, and deleted. You can implement this interface for local storage, cloud storage (e.g. S3), or in-memory/testing.
- **Asset Service:** The `AssetService` provides methods to upload, find, delete, and stream assets.

### Linking Assets to Content

You reference assets in your content by storing their IDs in the content data array.  
For example, a content entry with an image and a gallery might look like this:

```php
$data = [
    'title' => 'Example',
    'imageId' => 'asset_123',                // Single asset reference
    'gallery' => ['asset_123', 'asset_456'], // Multiple asset references
];
$content = $service->createContent('c1', $contentType, $data);
```

To retrieve the actual asset objects, use the `AssetService`:

```php
$imageAsset = $assetService->findAsset($content->getData()['imageId']);
foreach ($content->getData()['gallery'] as $assetId) {
    $galleryAsset = $assetService->findAsset($assetId);
    // ...process asset
}
```

### Uploading and Managing Assets

Assets are uploaded and managed via the `AssetService`:

```php
use Polyctopus\Core\Models\Asset;

// Create an Asset object (metadata)
$asset = new Asset(
    id: 'asset_123',
    filename: 'picture.jpg',
    mimeType: 'image/jpeg',
    size: 123456,
    storagePath: '/uploads/picture.jpg'
);

// Upload the asset with file content
$assetService->uploadAsset($asset, $fileContent);

// Delete an asset
$assetService->deleteAsset('asset_123');

// Get a stream for download or processing
$stream = $assetService->getAssetStream('asset_123');
```

### Implementation Note

Polyctopus Core only defines the interface for asset storage (`AssetRepositoryInterface`).  
You are free to implement this interface for your preferred storage backend (filesystem, cloud, etc.).  
This keeps your application flexible and decoupled from any specific storage technology.

**Best Practice:**  
Store only the asset IDs in your content.  
Use the `AssetService` to resolve asset metadata and file content when needed.

---

## Extending

- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage by extending `ContentRepositoryInterface` and `ContentVersionRepositoryInterface`.
- Create custom validation rules by implementing `FieldTypeInterface` or custom logic in your service.
- Use the provided `TestFactory` for easy test data setup in your tests.

## Contributing

Contributions are welcome! Please create a pull request or open an issue for discussion.

## License

LGPL-3.0-or-later