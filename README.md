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

## Extending
- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage by extending `ContentRepositoryInterface` and `ContentVersionRepositoryInterface`.
- Create custom validation rules by implementing `FieldTypeInterface` or custom logic in your service.
- Use the provided `TestFactory` for easy test data setup in your tests.

## Usage Example
See the `example-usage.php` file in the repository for a complete example of how to use the library.

## Content Types
- [Content Types](docs/ContentTypes.md)

## Content Variants (Dimension Overrides)
- [Content Variants](docs/ContentVariants.md)

## Multilingual Content & Translations
- [Translations](docs/Translations.md)

## Event Mechanism
- [Events](docs/Events.md)

## Versioning & Rollback
- [Versioning & Rollback](docs/Versioning.md)

## Asset Handling
- [Asset Handling](docs/Assets.md)

## Contributing
Contributions are welcome! Please create a pull request or open an issue for discussion.

## License

LGPL-3.0-or-later