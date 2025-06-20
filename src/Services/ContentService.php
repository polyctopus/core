<?php

namespace Polysync\Core\Services;

use Polysync\Core\Models\Content;
use Polysync\Core\Repositories\ContentRepositoryInterface;
use Polysync\Core\Repositories\ContentTypeRepositoryInterface;
use Polysync\Core\Models\ContentType;

use DateTimeImmutable;
use InvalidArgumentException;
use Polysync\Core\Models\ContentStatus;

class ContentService
{
    public function __construct(
        private readonly ContentRepositoryInterface $repository,
        private readonly ContentTypeRepositoryInterface $contentTypeRepository
    ) {}

    public function create(string $id, ContentType $contentType, array $data): Content
    {
        $this->validateContentData($contentType, $data);

        $content = new Content(
            $id,
            $contentType,
            ContentStatus::Draft,
            $data,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );

        $this->repository->save($content);
        return $content;
    }

    public function update(Content $content, ContentStatus $contentStatus, array $data): void
    {
        $this->validateContentData($content->getContentType(), $data);

        $arr = $content->toArray();
        $arr['data'] = $data;
        $arr['status'] = $contentStatus;
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

    private function validateContentData(ContentType $contentType, array $data): void
    {
        foreach ($contentType->getFields() as $field) {
            $code = $field->code;
            if (array_key_exists($code, $data) && !$field->validate($data[$code])) {
                throw new InvalidArgumentException("Validation failed for field '{$code}' with value: " . var_export($data[$code], true));
            }
        }
    }
}