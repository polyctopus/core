<?php
namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Repositories\ContentTypeRepositoryInterface;

final class ContentTypeService
{
    public function __construct(
        private readonly ContentTypeRepositoryInterface $contentTypeRepository
    ) {}

    public function createContentType(ContentType $contentType): void
    {
        if ($this->contentTypeRepository->find($contentType->getId())) {
            throw new \InvalidArgumentException("ContentType '{$contentType->getId()}' already exists.");
        }
        $this->contentTypeRepository->save($contentType);
    }

    public function updateContentType(ContentType $contentType): void
    {
        if (! $this->contentTypeRepository->find($contentType->getId())) {
            throw new \InvalidArgumentException("ContentType '{$contentType->getId()}' does not exist.");
        }
        $this->contentTypeRepository->save($contentType);
    }

    public function deleteContentType(string $contentTypeId): void
    {
        if (! $this->contentTypeRepository->find($contentTypeId)) {
            throw new \InvalidArgumentException("ContentType '{$contentTypeId}' does not exist.");
        }
        $this->contentTypeRepository->delete($contentTypeId);
    }

    public function findContentType(string $contentTypeId): ?ContentType
    {
        return $this->contentTypeRepository->find($contentTypeId);
    }

    public function listContentTypes(): array
    {
        return $this->contentTypeRepository->all();
    }
}