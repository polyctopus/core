<?php

use Polyctopus\Core\Services\ContentService;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\ContentType;

it('can create content via ContentService', function () {
    $repo = new InMemoryContentRepository();
    $service = new ContentService($repo, new InMemoryContentTypeRepository());

    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $content = $service->create('c1', $contentType, ['title' => 'Test']);

    expect($content)->toBeInstanceOf(Content::class)
        ->and($content->getId())->toBe('c1')
        ->and($content->getContentType()->getId())->toBe('ct1')
        ->and($content->getData())->toMatchArray(['title' => 'Test'])
        ->and($repo->find('c1'))->not->toBeNull();
});

it('can update content via ContentService', function () {
    $repo = new InMemoryContentRepository();
     $service = new ContentService($repo, new InMemoryContentTypeRepository());



    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $content = $service->create('c2', $contentType, ['title' => 'Old']);
    $service->update($content,ContentStatus::Published, ['title' => 'New']);

    $updated = $repo->find('c2');
    expect($updated)->toBeInstanceOf(Content::class)
        ->and($updated->getData())->toMatchArray(['title' => 'New']);
});

it('can find content via ContentService', function () {
    $repo = new InMemoryContentRepository();
    $service = new ContentService($repo, new InMemoryContentTypeRepository());


    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $service->create('c3', $contentType, ['foo' => 'bar']);
    $found = $service->find('c3');

    expect($found)->toBeInstanceOf(Content::class)
        ->and($found->getId())->toBe('c3');
});

it('can delete content via ContentService', function () {
    $repo = new InMemoryContentRepository();
     $service = new ContentService($repo, new InMemoryContentTypeRepository());

    $contentType = new ContentType('ct1', 'Type 1', 'Label');
    $service->create('c4', $contentType, ['x' => 1]);

    expect($repo->find('c4'))->not->toBeNull();

    $service->delete('c4');
    expect($repo->find('c4'))->toBeNull();
});

it('can list all content types via ContentService', function () {
    $repo = new InMemoryContentRepository();
    $contentTypeRepo = new InMemoryContentTypeRepository();

    $type1 = new ContentType('ct1', 'Type 1', 'Label 1');
    $type2 = new ContentType('ct2', 'Type 2', 'Label 2');
    $contentTypeRepo->save($type1);
    $contentTypeRepo->save($type2);

    $service = new ContentService($repo, $contentTypeRepo);

    $types = $service->listContentTypes();

    $ids = array_map(fn($type) => $type->getId(), $types);

    expect($types)->toBeArray()
        ->and($types)->toHaveCount(2)
        ->and($types[0])->toBeInstanceOf(ContentType::class)
        ->and($types[1])->toBeInstanceOf(ContentType::class)
        ->and($ids)->toContain('ct1')
        ->and($ids)->toContain('ct2');
});