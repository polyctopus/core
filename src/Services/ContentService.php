<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\{
    Events\ContentCreated,
    Events\ContentDeleted,
    Events\ContentUpdated,
    Events\ContentRolledBack,
    Exceptions\ValidationException,
    Models\Content,
    Models\ContentStatus,
    Models\ContentType,
    Models\ContentVersion,
    Repositories\ContentRepositoryInterface
};

use DateTimeImmutable;
use Polyctopus\Core\Models\EntityType;

class ContentService
{
    use EventDispatcherTrait;

    public function __construct(
        private readonly ContentRepositoryInterface $contentRepository,
        public readonly ContentTypeService $contentTypeService,
        public readonly ContentVariantService $contentVariantService,
        public readonly ContentTranslationService $contentTranslationService,
        public readonly ContentVersionService $contentVersionService,
        ?callable $eventDispatcher = null
    ) {
        if ($eventDispatcher) {
            $this->setEventDispatcher($eventDispatcher);
        }
    }

    public function createContent(string $id, ContentType $contentType, array $data): Content
    {
        $existingType = $this->contentTypeService->findContentType($contentType->getId());
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

        $this->contentRepository->save($content);
        $this->dispatch(new ContentCreated($content));

        $version = new ContentVersion(
            id: uniqid('ver_', true),
            entityType: 'content',
            entityId: $content->getId(),
            snapshot: $content->getData(),
            diff: null
        );
        $this->contentVersionService->saveContentVersion($version);

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
        
        $this->contentVersionService->saveContentVersion($version);

        $content->setStatus($contentStatus);
        $content->setData($newData);
        $this->contentRepository->save($content);
        $this->dispatch(new ContentUpdated($content));
    }

    public function findContent(string $id): ?Content
    {
        return $this->contentRepository->find($id);
    }

    public function deleteContent(string $id): void
    {
        $this->contentRepository->delete($id);
        $this->dispatch(new ContentDeleted($id));
    }

    public function rollback(string $entityId, string $versionId): void
    {
        $versions = $this->contentVersionService->findContentVersionsByEntityType('content', $entityId);

        /** @var ContentVersion $version */
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
        $content = $this->contentRepository->find($entityId);
        if (! $content) {
            throw new \RuntimeException("Content not found");
        }
        // Snapshot wiederherstellen
        $content->setData($version->toArray()['snapshot']);
        $this->contentRepository->save($content);
        $this->dispatch(new ContentRolledBack($content, $version));
    }

    public function resolveContentWithVariant(string $contentId, string $dimension): ?array
    {
        $content = $this->contentRepository->find($contentId);
        if (!$content) {
            return null;
        }
        $variant = $this->contentVariantService->findContentVariant($contentId, $dimension);

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

    public function resolveContentWithVariantAndLocale(string $contentId, string $dimension, string $locale): ?array
    {
        $content = $this->contentRepository->find($contentId);
        if (!$content) {
            return null;
        }

        $variant = $this->contentVariantService->findContentVariant($contentId, $dimension);

        $data = $content->getData();
        if ($variant) {
            $data = array_merge($data, $variant->getOverrides());
            $translation = $this->contentTranslationService->getTranslation(EntityType::Variant, $variant->getId(), $locale);
        } else {
            $translation = $this->contentTranslationService->getTranslation(EntityType::Content, $contentId, $locale);
        }
        if ($translation) {
            $data = array_merge($data, $translation->getFields());
        }
        return $data;
    }
}