<?php

require_once __DIR__ . '/vendor/autoload.php';

use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Models\ContentField;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\FieldTypes\TextFieldType;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Services\ContentService;

// Setup repositories
$contentRepo = new InMemoryContentRepository();
$contentTypeRepo = new InMemoryContentTypeRepository();

// Create a ContentType (e.g. "Article" with a "title" field)
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

// Save ContentType
$contentTypeRepo->save($articleType);

// Create ContentService
$service = new ContentService($contentRepo, $contentTypeRepo);

// Create a new Content (valid)
$content = $service->create('c1', $articleType, ['title' => 'Hello World!']);
echo "Created content: " . print_r($content->toArray(), true) . PHP_EOL;

// Update Content (valid and status change)
$service->update($content, ContentStatus::Published, ['title' => 'Updated Title']);
$updated = $service->find('c1');
echo "Updated content: " . print_r($updated->toArray(), true) . PHP_EOL;

// Try to update with invalid data (should throw exception)
try {
    $service->update($content, ContentStatus::Published, ['title' => str_repeat('A', 300)]);
} catch (\InvalidArgumentException $e) {
    echo "Validation failed as expected: " . $e->getMessage() . PHP_EOL;
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