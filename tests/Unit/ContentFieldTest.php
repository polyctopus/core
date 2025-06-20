<?php
// tests/ContentFieldTest.php

use Polysync\Core\Models\ContentField;

it('can construct and serialize a ContentField', function () {
    $now = new DateTimeImmutable();
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'price',
        label: 'Price',
        fieldType: new Polysync\Core\Models\FieldTypes\TextFieldType(), 
        settings: ['min' => 0, 'max' => 100],
        sortOrder: 10,
        createdAt: $now,
        updatedAt: $now
    );

    // Test getters
    expect($field->getId())->toBe('f1')
        ->and($field->getContentTypeId())->toBe('ct1')
        ->and($field->getCode())->toBe('price')
        ->and($field->getLabel())->toBe('Price')
        ->and(get_class($field->getFieldType()))->toBe(Polysync\Core\Models\FieldTypes\TextFieldType::class)
        ->and($field->getSettings())->toMatchArray(['min' => 0, 'max' => 100])
        ->and($field->getSortOrder())->toBe(10);

    // Test toArray()/fromArray()
    $arr = $field->toArray();
    $reconstructed = ContentField::fromArray($arr);

    expect($reconstructed)->toBeInstanceOf(ContentField::class);
    expect($reconstructed->toArray())->toMatchArray($arr);
});

it('validates a Text field type', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'title',
        label: 'Title',
        fieldType: new Polysync\Core\Models\FieldTypes\TextFieldType(),
        settings: ['maxLength' => 5]
    );

    // Ensure the validate method exists and works as expected
    expect(method_exists($field, 'validate'))->toBeTrue();
    expect($field->validate('Hello'))->toBeTrue();
    expect($field->validate('Hello World'))->toBeFalse();
});