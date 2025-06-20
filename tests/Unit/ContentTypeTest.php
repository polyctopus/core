<?php

use Polysync\Core\Models\ContentType;
use Polysync\Core\Models\ContentField;

it('can construct and manipulate a ContentType', function () {
    $field = new ContentField(
        id: 'f1',
        contentTypeId: 'ct1',
        code: 'name',
        label: 'Name',
        fieldType: new Polysync\Core\Models\FieldTypes\TextFieldType()
    );

    $type = new ContentType(
        id: 'ct1',
        code: 'articles',
        label: 'Articles',
        fields: []
    );

    expect($type->getId())->toBe('ct1')
        ->and($type->getCode())->toBe('articles')
        ->and($type->getLabel())->toBe('Articles');

    // Test adding a field
    $type->addField($field);
    expect($type->getFields())->toHaveCount(1)
        ->and($type->getFields()[0])->toBeInstanceOf(ContentField::class);

    // Test removing a field
    $type->removeField('f1');
    expect($type->getFields())->toBeEmpty();
});
