<?php

use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\ContentVariant;
use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Models\ContentField;
use Polyctopus\Core\Models\FieldTypes\TextFieldType;
use Polyctopus\Core\Exceptions\ValidationException;
use Polyctopus\Core\Services\InMemoryContentServiceFactory;

beforeEach(function () {
    // Initialize repositories and service
    $this->service = InMemoryContentServiceFactory::create();
});

it('can create content via ContentService', function () {
    $contentType = new ContentType('ct1', 'ctype_1', 'Label');
    $this->service->createContentType($contentType); 
    $content = $this->service->createContent('ct1', $contentType, ['title' => 'Test']);

    expect($content)->toBeInstanceOf(Content::class)
        ->and($content->getId())->toBe('ct1')
        ->and($content->getContentType()->getId())->toBe('ct1')
        ->and($content->getData())->toMatchArray(['title' => 'Test'])
        ->and($this->service->findContent('ct1'))->not->toBeNull();
});

it('can update content via ContentService', function () {
    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $this->service->createContentType($contentType); 
    $content = $this->service->createContent('c2', $contentType, ['title' => 'Old']);
    $this->service->updateContent($content, ContentStatus::Published, ['title' => 'New']);

    $updated = $this->service->findContent('c2');
    expect($updated)->toBeInstanceOf(Content::class)
        ->and($updated->getData())->toMatchArray(['title' => 'New']);
});

it('can find content via ContentService', function () {
    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $this->service->createContentType($contentType); 
    $this->service->createContent('c3', $contentType, ['foo' => 'bar']);
    $found = $this->service->findContent('c3');

    expect($found)->toBeInstanceOf(Content::class)
        ->and($found->getId())->toBe('c3');
});

it('can delete content via ContentService', function () {
    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $this->service->createContentType($contentType); 
    $this->service->createContent('c4', $contentType, ['x' => 1]);

    expect($this->service->findContent('c4'))->not->toBeNull();

    $this->service->deleteContent('c4');
    expect($this->service->findContent('c4'))->toBeNull();
});

it('can list all content types via ContentService', function () {

    $type1 = new ContentType('ct1', 'Type 1', 'Label 1');
    $type2 = new ContentType('ct2', 'Type 2', 'Label 2');
    $this->service->createContentType($type1);
    $this->service->createContentType($type2);

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
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 5]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $this->service->createContentType($contentType);

    expect(fn() => $this->service->createContent('c5', $contentType, ['title' => 'Too long for field']))
        ->toThrow(ValidationException::class);
});

it('throws exception if content data does not match field validation on update', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 5]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $this->service->createContentType($contentType);

    $content = $this->service->createContent('c6', $contentType, ['title' => 'Short']);
    expect(fn() => $this->service->updateContent($content, ContentStatus::Draft, ['title' => 'Too long for field']))
        ->toThrow(ValidationException::class);
});

it('creates a version entry when updating content', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $this->service->createContentType($contentType);
    $content = $this->service->createContent('c7', $contentType, ['title' => 'Original']);

    $this->service->updateContent($content, ContentStatus::Published, ['title' => 'Changed']);
    $versions = $this->service->listAllContentVersions();
    expect($versions)->toBeArray()
        ->and(count($versions))->toBeGreaterThan(0)
        ->and($versions[array_key_first($versions)])->getSnapshot()->toMatchArray(['title' => 'Original'])
        ->and($versions[array_key_first(array_slice($versions, 1, 1, true))])->getDiff()->toBe(json_encode(['title' => 'Changed']));
});

it('can rollback content to a previous version', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $this->service->createContentType($contentType);

    $content = $this->service->createContent('ct1', $contentType, ['title' => 'First']);
    $this->service->updateContent($content, ContentStatus::Published, ['title' => 'Second']);
    $this->service->updateContent($content, ContentStatus::Published, ['title' => 'Third']);

    // Simulate rollback: get first version and restore its snapshot
    $versions = $this->service->listAllContentVersions();
    $firstVersion = $versions[array_key_first($versions)];
    $this->service->updateContent($content, ContentStatus::Published, $firstVersion->getSnapshot());

    $rolledBack = $this->service->findContent('ct1');
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
    $this->service->createContentType($contentType);
    $this->service->createContent('c9', $contentType, ['title' => 'Original Title']);
    
    $variant = new ContentVariant(
        id: 'v1',
        contentId: 'c9',
        dimension: 'brand_a',
        overrides: ['title' => 'Brand A Title']
    );
    $this->service->createContentVariant($variant);

    $resolved = $this->service->resolveContentWithVariant('c9', 'brand_a');
    expect($resolved)->toBe(['title' => 'Brand A Title']);

    $resolvedDefault = $this->service->resolveContentWithVariant('c9', 'brand_b');
    expect($resolvedDefault)->toBe(['title' => 'Original Title']);
});

it('can add and resolve translations for content and variants', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );
    $contentType = new ContentType('ct1', 'Type 1', 'Label', [$field]);
    $this->service->createContentType($contentType);

    // Content anlegen
    $content = $this->service->createContent('c10', $contentType, ['title' => 'Original Title']);

    // Übersetzung für Content hinzufügen
    $this->service->addOrUpdateTranslation('content', 'c10', 'de_DE', ['title' => 'Deutscher Titel']);

    // Content mit Übersetzung auflösen
    $resolved = $this->service->resolveContentWithVariantAndLocale('c10', '', 'de_DE');
    expect($resolved)->toBe(['title' => 'Deutscher Titel']);

    // Variante anlegen
    $variant = new ContentVariant(
        id: 'v10',
        contentId: 'c10',
        dimension: 'brand_x',
        overrides: ['title' => 'Brand X Title']
    );
    $this->service->createContentVariant($variant);

    // Übersetzung für Variante hinzufügen
    $this->service->addOrUpdateTranslation('variant', 'v10', 'de_DE', ['title' => 'Marke X Titel']);

    // Variante mit Übersetzung auflösen
    $resolvedVariant = $this->service->resolveContentWithVariantAndLocale('c10', 'brand_x', 'de_DE');
    expect($resolvedVariant)->toBe(['title' => 'Marke X Titel']);

    // Variante ohne Übersetzung (soll override zeigen)
    $resolvedVariantNoTrans = $this->service->resolveContentWithVariantAndLocale('c10', 'brand_x', 'fr_FR');
    expect($resolvedVariantNoTrans)->toBe(['title' => 'Brand X Title']);

    // Content ohne Übersetzung (soll Original zeigen)
    $resolvedNoTrans = $this->service->resolveContentWithVariantAndLocale('c10', '', 'fr_FR');
    expect($resolvedNoTrans)->toBe(['title' => 'Original Title']);
});