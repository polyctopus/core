<?php

require_once __DIR__ . '/vendor/autoload.php';

use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\ContentVariant;
use Polyctopus\Core\Services\TestFactory;
use Polyctopus\Core\Services\ContentServiceFactory;

// Create ContentService
$service = ContentServiceFactory::create();

// Create a ContentType (e.g. "Article" with a "title" field using the TestFactory)
$contentType = TestFactory::contentTypeWithTextField('article');

$service->createContentType($contentType);

// Create a new Content (invalid)
try {
    $content = $service->createContent('c1', $contentType, ['title' => str_repeat('A', 300)]);

} catch (\Polyctopus\Core\Exceptions\ValidationException $e) {
    foreach ($e->getErrors() as $error) {
        echo "Field: {$error->field}, Value: " . var_export($error->value, true) . ", Message: {$error->message}\n";
    }
}

// Create a new Content (valid one)
$content = $service->createContent('c1', $contentType, ['title' => str_repeat('A', 5)]);

echo "Created content: " . print_r($content->toArray(), true) . PHP_EOL;

// Update Content (valid and status change)
$service->updateContent($content, ContentStatus::Published, ['title' => 'Updated Title']);
$updated = $service->findContent('c1');
echo "Updated content: " . print_r($updated->toArray(), true) . PHP_EOL;

// Weitere Updates für Versionierung
$service->updateContent($content, ContentStatus::Published, ['title' => 'Second Version']);
$service->updateContent($content, ContentStatus::Published, ['title' => 'Third Version']);
echo "Content after more updates: " . print_r($service->findContent('c1')->toArray(), true) . PHP_EOL;

// Zeige alle Versionen
$versions = $service->findContentVersionsByEntityType('content', 'c1');
echo "Available versions for content c1:" . PHP_EOL;
foreach ($versions as $version) {
    echo "- Version ID: {$version->getId()}, Snapshot: " . json_encode($version->toArray()['snapshot']) . PHP_EOL;
}

// Rollback auf die erste Version
echo "Rolling back to the first version..." . PHP_EOL;
$firstVersion = reset($versions);
if ($firstVersion) {
    $service->rollback('c1', $firstVersion->getId());
    $rolledBack = $service->findContent('c1');
    echo "Content after rollback: " . print_r($rolledBack->toArray(), true) . PHP_EOL;
}


// Create a brand content variant
// (e.g. for a specific brand dimension)
echo "Creating content variant for brand dimension..." . PHP_EOL;
$variant = new ContentVariant(
    id: 'v1',
    contentId: 'c1',
    dimension: 'brand_a',
    overrides: ['title' => 'Brand A Title']
);
$service->createContentVariant($variant);

// resolve content with variant overrides
$resolvedBrandA = $service->resolveContentWithVariant('c1', 'brand_a');
echo "Resolved content for dimension 'brand_a': " . print_r($resolvedBrandA, true) . PHP_EOL;

// resolve content without variant (default)
$resolvedDefault = $service->resolveContentWithVariant('c1', 'brand_b');
echo "Resolved content for dimension 'brand_b' (no variant): " . print_r($resolvedDefault, true) . PHP_EOL;



// List all ContentTypes
echo "Listing all content types:" . PHP_EOL;
$contentTypes = $service->listContentTypes();
echo "Available content types:" . PHP_EOL;
foreach ($contentTypes as $ct) {
    echo "- {$ct->getId()}: {$ct->getLabel()}" . PHP_EOL;
}

// Delete Content
$service->deleteContent('c1');
echo "Content after delete: ";
var_dump($service->findContent('c1'));

echo  PHP_EOL ."Memory usage of this script: ";
echo round(memory_get_usage()/1024/1024,2) . " MBytes \n" . PHP_EOL;