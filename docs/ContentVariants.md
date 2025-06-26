
# Content Variants (Dimension Overrides)

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
