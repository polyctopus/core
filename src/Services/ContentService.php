<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentStatus;
use Polyctopus\Core\Models\ContentVersion;
use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Repositories\ContentRepositoryInterface;
use Polyctopus\Core\Repositories\ContentTypeRepositoryInterface;
use Polyctopus\Core\Repositories\ContentVersionRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;

class ContentService
{
    public function __construct(
        private readonly ContentRepositoryInterface $repository,
        private readonly ContentTypeRepositoryInterface $contentTypeRepository,
        private readonly ContentVersionRepositoryInterface $contentVersionRepository
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

        // NEU: Erste Version anlegen
        $version = new ContentVersion(
            id: uniqid('ver_', true),
            entityType: 'content',
            entityId: $content->getId(),
            snapshot: $content->getData(),
            diff: null // keine Änderung, da initial
        );
        $this->contentVersionRepository->save($version);

        return $content;
    }

    public function update(Content $content, ContentStatus $contentStatus, array $newData): void
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

        $content->setData($newData);
        $this->repository->save($content);
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