<?php

use Polysync\Core\Models\Content;
use Polysync\Core\Models\ContentType;
use Polysync\Core\Models\ContentField;
use Polysync\Core\Models\ContentStatus;
use Polysync\Core\Models\FieldTypes\TextFieldType;
use Polysync\Core\Repositories\InMemory\InMemoryContentRepository;

it('can save and retrieve Content via repository', function () {
    $repo = new InMemoryContentRepository();

    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new TextFieldType(),
        settings: ['maxLength' => 255]
    );

    $contentType = new ContentType(
        id: 'ct1',
        code: 'hotel',
        label: 'Hotel',
        fields: [$field]
    );

    $content = new Content(
        id: 'c1',
        contentType: $contentType,
        data: [
            'title' => 'Test Hotel',
            'price' => 99.99,
            'status' => 'published'
        ],
        status: ContentStatus::Published,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable()
    );

    $repo->save($content);

    $retrieved = $repo->find('c1');

    expect($retrieved)->toBeInstanceOf(Content::class)
        ->and($retrieved->getId())->toBe('c1')
        ->and($retrieved->getContentType()->getId())->toBe('ct1')
        ->and($retrieved->getData())->toMatchArray([
            'title' => 'Test Hotel',
            'price' => 99.99
        ])
        ->and($retrieved->getStatus())->toBe(ContentStatus::Published);
});

it('returns null when content not found', function () {
    $repo = new InMemoryContentRepository();
    $retrieved = $repo->find('does-not-exist');
    expect($retrieved)->toBeNull();
});

it('can delete content', function () {
    $repo = new InMemoryContentRepository();

    $contentType = new ContentType(
        id: 'ct1',
        code: 'hotel',
        label: 'Hotel',
        fields: []
    );

    $content = new Content(
        id: 'c2',
        contentType: $contentType,
        data: ['title' => 'To be deleted'],
        status: ContentStatus::Draft,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable()
    );

    $repo->save($content);
    expect($repo->find('c2'))->not->toBeNull();

    $repo->delete('c2');
    expect($repo->find('c2'))->toBeNull();
});