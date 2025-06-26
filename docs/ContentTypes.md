# Content Types

Content types define the structure (fields, validation, etc.) for your content entries.  
You should always create and register a content type before creating content entries of that type.

```php
use Polyctopus\Core\Services\TestFactory;

// Create a ContentType (e.g. "Article" with a "title" and "contact" field)
$contentType = TestFactory::contentTypeWithTextField('article');
$service->contentTypeService->createContentType($contentType);
```