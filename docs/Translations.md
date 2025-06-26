# Multilingual Content & Translations

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
$service->contentTranslationService->addOrUpdateTranslation('content', 'c1', 'de_DE', ['title' => 'Hallo Welt']);

// Add a translation for a variant
$service->contentTranslationService->addOrUpdateTranslation('variant', 'v1', 'de_DE', ['title' => 'Marke A Titel']);

// Resolve content with variant and locale
$data = $service->resolveContentWithVariantAndLocale('c1', 'brand_a', 'de_DE');
echo "Resolved content translation for dimension 'brand_a': " . print_r($data, true) . PHP_EOL;

// If no translation exists for the given locale, the original (or variant) data is returned.
```