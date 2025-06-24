<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Exceptions\ValidationException;
use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\ContentVariant;
use Polyctopus\Core\Models\ContentVersion;
use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Repositories\ContentRepositoryInterface;
use Polyctopus\Core\Repositories\ContentTypeRepositoryInterface;
use Polyctopus\Core\Repositories\ContentVariantRepositoryInterface;
use Polyctopus\Core\Repositories\ContentVersionRepositoryInterface;
use DateTimeImmutable;

class ContentService
{
    public function __construct(
        private readonly ContentRepositoryInterface $repository,
        private readonly ContentTypeRepositoryInterface $contentTypeRepository,
        private readonly ContentVersionRepositoryInterface $contentVersionRepository,
        private readonly ContentVariantRepositoryInterface $contentVariantRepository
    ) {}

    public function createContent(string $id, ContentType $contentType, array $data): Content
    {
        $existingType = $this->contentTypeRepository->find($contentType->getId());
        if (!$existingType) {
            throw new \InvalidArgumentException("ContentType '{$contentType->getId()}' does not exist.");
        }

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

        $version = new ContentVersion(
            id: uniqid('ver_', true),
            entityType: 'content',
            entityId: $content->getId(),
            snapshot: $content->getData(),
            diff: null
        );
        $this->contentVersionRepository->save($version);

        return $content;
    }

    public function updateContent(Content $content, ContentStatus $contentStatus, array $newData): void
    {
        $this->validateContentData($content->getContentType(), $newData);

        $oldSnapshot = $content->getData();
        $diff = json_encode(array_diff_assoc($newData, $oldSnapshot));
        $version = new ContentVersion(
            id: uniqid('ver_', true),
            entityType: 'content',
            entityId: $content->getId(),
            snapshot: $newData,
            diff: $diff
        );
        
        $this->contentVersionRepository->save($version);

        $content->setStatus($contentStatus);
        $content->setData($newData);
        $this->repository->save($content);
    }

    public function findContent(string $id): ?Content
    {
        return $this->repository->find($id);
    }

    public function deleteContent(string $id): void
    {
        $this->repository->delete($id);
    }

    public function listContentTypes(): array
    {
        return $this->contentTypeRepository->all();
    }

    public function rollback(string $entityId, string $versionId): void
    {
        $versions = $this->contentVersionRepository->findByEntity('content', $entityId);
        $version = null;
        foreach ($versions as $v) {
            if (method_exists($v, 'getId') && $v->getId() === $versionId) {
                $version = $v;
                break;
            } elseif (is_array($v) && isset($v['id']) && $v['id'] === $versionId) {
                $version = $v;
                break;
            }
        }
        if (! $version) {
            throw new \RuntimeException("Version not found");
        }
        $content = $this->repository->find($entityId);
        if (! $content) {
            throw new \RuntimeException("Content not found");
        }
        // Snapshot wiederherstellen
        $content->setData($version->toArray()['snapshot']);
        $this->repository->save($content);
    }

    public function resolveContentWithVariant(string $contentId, string $dimension): ?array
    {
        $content = $this->repository->find($contentId);
        if (!$content) {
            return null;
        }
        $variant = $this->contentVariantRepository->findByContentAndDimension($contentId, $dimension);

        $data = $content->getData();
        if ($variant) {
            $data = array_merge($data, $variant->getOverrides());
        }
        return $data;
    }

    private function validateContentData(ContentType $contentType, array $data): void
    {
        $errors = [];
        foreach ($contentType->getFields() as $field) {
            $code = $field->code;
            if (array_key_exists($code, $data) && !$field->validate($data[$code])) {
                $errors[] = new \Polyctopus\Core\Models\ValidationError(
                    field: $code,
                    value: $data[$code],
                    message: "Validation failed for field '{$code}' with value: " . var_export($data[$code], true)
                );
            }
        }
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

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

    public function findContentVariant(string $contentId, string $dimension): ?ContentVariant
    {
        return $this->contentVariantRepository->findByContentAndDimension($contentId, $dimension);
    }

    public function createContentVariant(ContentVariant $variant): void
    {
        $this->contentVariantRepository->save($variant);
    }

    public function deleteContentVariant(string $variantId): void
    {
        $this->contentVariantRepository->delete($variantId);
    }

    public function findContentVersionsByEntityType(string $entityType, string $entityId): array
    {
        return $this->contentVersionRepository->findByEntity($entityType, $entityId);
    }

    public function saveContentVersion(ContentVersion $version): void
    {
        $this->contentVersionRepository->save($version);
    }

    public function listAllContentVersions(): array
    {
        return $this->contentVersionRepository->all();
    }

}