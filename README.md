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

## Content Types

Content types define the structure (fields, validation, etc.) for your content entries.  
You should always create and register a content type before creating content entries of that type.

```php
use Polyctopus\Core\Services\TestFactory;

// Create a ContentType (e.g. "Article" with a "title" and "contact" field)
$contentType = TestFactory::contentTypeWithTextField('article');
$service->createContentType($contentType);
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
$service->createContentVariant($variant);

// Resolve content for "brand_a"
$resolved = $service->resolveContentWithVariant('c1', 'brand_a');
// $resolved will contain the overridden title for brand_a

// Resolve content for a dimension without a variant
$resolvedDefault = $service->resolveContentWithVariant('c1', 'brand_b');
// $resolvedDefault will contain the original content data
```

## Multilingual Content & Translations

Polyctopus Core supports optional translations for both content and content variants.  
Translations are identified by a locale string (e.g. `de_DE`, `en_US`).  
You can add translations for the main content or for any variant.  
When resolving content for a specific locale, translations are merged over the original data or variant overrides.

**How it works:**
- Translations are optional and can be added at any time.
- You can add or update a translation for a content or variant by specifying the entity type (`'content'` or `'variant'`), the entity ID, the locale, and the translated fields.
- When resolving content with a variant and locale, the service merges the translation fields over the variant (if present) or the original content.

**Example:**
```php
// Add a translation for the main content
$service->addOrUpdateTranslation('content', 'c1', 'de_DE', ['title' => 'Hallo Welt']);

// Add a translation for a variant
$service->addOrUpdateTranslation('variant', 'v1', 'de_DE', ['title' => 'Marke A Titel']);

// Resolve content with variant and locale
$data = $service->resolveContentWithVariantAndLocale('c1', 'brand_a', 'de_DE');
echo "Resolved content translation for dimension 'brand_a': " . print_r($data, true) . PHP_EOL;

// If no translation exists for the given locale, the original (or variant) data is returned.
```

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
$versions = $service->findContentVersionsByEntityType('content', 'c1');
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

## Extending

- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage by extending `ContentRepositoryInterface` and `ContentVersionRepositoryInterface`.
- Create custom validation rules by implementing `FieldTypeInterface` or custom logic in your service.
- Use the provided `TestFactory` for easy test data setup in your tests.

## Contributing

Contributions are welcome! Please create a pull request or open an issue for discussion.

## License

LGPL-3.0-or-later