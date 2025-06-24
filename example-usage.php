<?php

require_once __DIR__ . '/vendor/autoload.php';

use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVersionRepository;
use Polyctopus\Core\Services\ContentService;
use Polyctopus\Core\Services\TestFactory;

// Setup repositories
$contentRepo = new InMemoryContentRepository();
$contentTypeRepo = new InMemoryContentTypeRepository();
$contentVersionRepo = new InMemoryContentVersionRepository();

// Create a ContentType (e.g. "Article" with a "title" field using the TestFactory)
$contentType = TestFactory::contentTypeWithTextField('article');

// Save ContentType
$contentTypeRepo->save($contentType);

// Create ContentService
$service = new ContentService($contentRepo, $contentTypeRepo, $contentVersionRepo);

// Create a new Content (invalid)
try {
    $content = $service->create('c1', $articleType, ['title' => str_repeat('A', 300)]);

} catch (\Polyctopus\Core\Models\ValidationException $e) {
    foreach ($e->getErrors() as $error) {
        echo "Field: {$error->field}, Value: " . var_export($error->value, true) . ", Message: {$error->message}\n";
    }
}

// Create a new Content (valid one)
$content = $service->create('c1', $articleType, ['title' => str_repeat('A', 5)]);

echo "Created content: " . print_r($content->toArray(), true) . PHP_EOL;

// Update Content (valid and status change)
$service->update($content, ContentStatus::Published, ['title' => 'Updated Title']);
$updated = $service->find('c1');
echo "Updated content: " . print_r($updated->toArray(), true) . PHP_EOL;

// Weitere Updates für Versionierung
$service->update($content, ContentStatus::Published, ['title' => 'Second Version']);
$service->update($content, ContentStatus::Published, ['title' => 'Third Version']);
echo "Content after more updates: " . print_r($service->find('c1')->toArray(), true) . PHP_EOL;

// Zeige alle Versionen
$versions = $contentVersionRepo->findByEntity('content', 'c1');
echo "Available versions for content c1:" . PHP_EOL;
foreach ($versions as $version) {
    echo "- Version ID: {$version->getId()}, Snapshot: " . json_encode($version->toArray()['snapshot']) . PHP_EOL;
}

// Rollback auf die erste Version
$firstVersion = reset($versions);
if ($firstVersion) {
    $service->rollback('c1', $firstVersion->getId());
    $rolledBack = $service->find('c1');
    echo "Content after rollback: " . print_r($rolledBack->toArray(), true) . PHP_EOL;
}

// List all ContentTypes
$contentTypes = $service->listContentTypes();
echo "Available content types:" . PHP_EOL;
foreach ($contentTypes as $ct) {
    echo "- {$ct->getId()}: {$ct->getLabel()}" . PHP_EOL;
}

// Delete Content
$service->delete('c1');
echo "Content after delete: ";
var_dump($service->find('c1'));

echo  PHP_EOL ."Memory usage of this script: ";
echo round(memory_get_usage()/1024/1024,2) . " MBytes \n" . PHP_EOL;