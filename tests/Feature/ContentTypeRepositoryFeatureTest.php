<?php

use Polysync\Core\Models\ContentType;
use Polysync\Core\Models\ContentField;
use Polysync\Core\Repositories\InMemory\InMemoryContentTypeRepository;

it('can save and retrieve ContentType via repository', function () {
    $repo = new InMemoryContentTypeRepository();

    $field = new ContentField(
        'f1',
        'ct1',
        'title',
        'Title',
        new Polysync\Core\Models\FieldTypes\TextFieldType(),
        ['maxLength' => 255],
    );

    $contentType = new ContentType(
        'ct1',
        'articles',
        'Articles',
        []
    );
    $contentType->addField($field);

    $repo->save($contentType);
    $retrieved = $repo->find('ct1');

    expect($retrieved)->toBeInstanceOf(ContentType::class)
        ->and($retrieved->getId())->toBe('ct1')
        ->and($retrieved->getFields())->toHaveCount(1)
        ->and($retrieved->getFields()[0]->getCode())->toBe('title');
});

it('returns null when content type not found', function () {
    $repo = new InMemoryContentTypeRepository();
    $retrieved = $repo->find('non-existing-id');
    expect($retrieved)->toBeNull();
});
