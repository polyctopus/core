<?php

use Polyctopus\Core\Services\ContentService;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVersionRepository;
use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\ContentVariant;
use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Models\ContentField;
use Polyctopus\Core\Models\FieldTypes\TextFieldType;
use Polyctopus\Core\Models\ValidationException;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVariantRepository;

beforeEach(function () {
    $this->repo = new InMemoryContentRepository();
    $this->contentTypeRepo = new InMemoryContentTypeRepository();
    $this->contentVersionRepo = new InMemoryContentVersionRepository();
    $this->contentVariantRepo = new InMemoryContentVariantRepository();
    $this->service = new ContentService(
        $this->repo,
        $this->contentTypeRepo,
        $this->contentVersionRepo,
        $this->contentVariantRepo
    );
});

it('can create content via ContentService', function () {

    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $content = $this->service->create('c1', $contentType, ['title' => 'Test']);

    expect($content)->toBeInstanceOf(Content::class)
        ->and($content->getId())->toBe('c1')
        ->and($content->getContentType()->getId())->toBe('ct1')
        ->and($content->getData())->toMatchArray(['title' => 'Test'])
        ->and($this->repo->find('c1'))->not->toBeNull();
});

it('can update content via ContentService', function () {
    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $content = $this->service->create('c2', $contentType, ['title' => 'Old']);
    $this->service->update($content, ContentStatus::Published, ['title' => 'New']);

    $updated = $this->repo->find('c2');
    expect($updated)->toBeInstanceOf(Content::class)
        ->and($updated->getData())->toMatchArray(['title' => 'New']);
});

it('can find content via ContentService', function () {
    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $this->service->create('c3', $contentType, ['foo' => 'bar']);
    $found = $this->service->find('c3');

    expect($found)->toBeInstanceOf(Content::class)
        ->and($found->getId())->toBe('c3');
});

it('can delete content via ContentService', function () {
    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $this->service->create('c4', $contentType, ['x' => 1]);

    expect($this->repo->find('c4'))->not->toBeNull();

    $this->service->delete('c4');
    expect($this->repo->find('c4'))->toBeNull();
});

it('can list all content types via ContentService', function () {

    $type1 = new ContentType('ct1', 'Type 1', 'Label 1');
    $type2 = new ContentType('ct2', 'Type 2', 'Label 2');
    $this->contentTypeRepo->save($type1);
    $this->contentTypeRepo->save($type2);

    $types = $this->service->listContentTypes();

    $ids = array_map(fn($type) => $type->getId(), $types);

    expect($types)->toBeArray()
        ->and($types)->toHaveCount(2)
        ->and($types[0])->toBeInstanceOf(ContentType::class)
        ->and($types[1])->toBeInstanceOf(ContentType::class)
        ->and($ids)->toContain('ct1')
        ->and($ids)->toContain('ct2');
});

it('throws exception if content data does not match field validation on create', function () {
    $repo = new InMemoryContentRepository();
    $contentTypeRepo = new InMemoryContentTypeRepository();
    $contentVersionRepo = new InMemoryContentVersionRepository();
    $contentVariantRepo = new InMemoryContentVariantRepository();

    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 5]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $service = new ContentService($repo, $contentTypeRepo, $contentVersionRepo, $contentVariantRepo);

    expect(fn() => $service->create('c5', $contentType, ['title' => 'Too long for field']))
        ->toThrow(ValidationException::class);
});

it('throws exception if content data does not match field validation on update', function () {
    $repo = new InMemoryContentRepository();
    $contentTypeRepo = new InMemoryContentTypeRepository();
    $contentVersionRepo = new InMemoryContentVersionRepository();
    $contentVariantRepo = new InMemoryContentVariantRepository();

    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 5]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $service = new ContentService($repo, $contentTypeRepo, $contentVersionRepo, $contentVariantRepo);

    $content = $service->create('c6', $contentType, ['title' => 'Short']);
    expect(fn() => $service->update($content, ContentStatus::Draft, ['title' => 'Too long for field']))
        ->toThrow(ValidationException::class);
});

it('creates a version entry when updating content', function () {
    $repo = new InMemoryContentRepository();
    $contentTypeRepo = new InMemoryContentTypeRepository();
    $contentVersionRepo = new InMemoryContentVersionRepository();
    $contentVariantRepo = new InMemoryContentVariantRepository();

    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $service = new ContentService($repo, $contentTypeRepo, $contentVersionRepo, $contentVariantRepo);
    $content = $service->create('c7', $contentType, ['title' => 'Original']);

    $service->update($content, ContentStatus::Published, ['title' => 'Changed']);

    $versions = $contentVersionRepo->all();
    expect($versions)->toBeArray()
        ->and(count($versions))->toBeGreaterThan(0)
        ->and($versions[array_key_first($versions)])->getSnapshot()->toMatchArray(['title' => 'Original'])
        ->and($versions[array_key_first(array_slice($versions, 1, 1, true))])->getDiff()->toBe(json_encode(['title' => 'Changed']));
});

it('can rollback content to a previous version', function () {
    $repo = new InMemoryContentRepository();
    $contentTypeRepo = new InMemoryContentTypeRepository();
    $contentVersionRepo = new InMemoryContentVersionRepository();
    $contentVariantRepo = new InMemoryContentVariantRepository();

    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $service = new ContentService($repo, $contentTypeRepo, $contentVersionRepo, $contentVariantRepo);

    $content = $service->create('c8', $contentType, ['title' => 'First']);
    $service->update($content, ContentStatus::Published, ['title' => 'Second']);
    $service->update($content, ContentStatus::Published, ['title' => 'Third']);

    // Simulate rollback: get first version and restore its snapshot
    $versions = $contentVersionRepo->all();
    $firstVersion = $versions[array_key_first($versions)];
    $service->update($content, ContentStatus::Published, $firstVersion->getSnapshot());

    $rolledBack = $repo->find('c8');
    expect($rolledBack->getData())->toMatchArray(['title' => 'First']);
});

it('can resolve content with variant overrides', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $this->contentTypeRepo->save($contentType);

    $this->service->create('c9', $contentType, ['title' => 'Original Title']);
    
    $variant = new ContentVariant(
        id: 'v1',
        contentId: 'c9',
        dimension: 'brand_a',
        overrides: ['title' => 'Brand A Title']
    );
    $this->contentVariantRepo->save($variant);

    $resolved = $this->service->resolveContentWithVariant('c9', 'brand_a');
    expect($resolved)->toBe(['title' => 'Brand A Title']);

    $resolvedDefault = $this->service->resolveContentWithVariant('c9', 'brand_b');
    expect($resolvedDefault)->toBe(['title' => 'Original Title']);
});