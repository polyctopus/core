<?php

namespace Polysync\Core\Services;

use Polysync\Core\Models\Content;
use Polysync\Core\Repositories\ContentRepositoryInterface;
use Polysync\Core\Repositories\ContentTypeRepositoryInterface;
use Polysync\Core\Models\ContentType;
use DateTimeImmutable;

class ContentService
{
    public function __construct(
        private ContentRepositoryInterface $repository,
        private ContentTypeRepositoryInterface $contentTypeRepository
    )
    {
        $this->repository = $repository;
        $this->contentTypeRepository = $contentTypeRepository;
    }

    public function create(string $id, ContentType $contentType, array $data): Content
    {
        $content = new Content(
            $id,
            $contentType,
            $data,
            'draft',
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $this->repository->save($content);
        return $content;
    }

    public function update(Content $content, array $data): void
    {
        $arr = $content->toArray();
        $arr['data'] = $data;
        $arr['updatedAt'] = (new DateTimeImmutable())->format(DATE_ATOM);

        $updated = Content::fromArray($arr);
        $this->repository->save($updated);
    }

    public function find(string $id): ?Content
    {
        return $this->repository->find($id);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }
    
    public function listContentTypes(): array
    {
        return $this->contentTypeRepository->all();
    }
}