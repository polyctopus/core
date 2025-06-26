<?php
namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\ContentVersion;
use Polyctopus\Core\Repositories\ContentVersionRepositoryInterface;

final class ContentVersionService
{
    public function __construct(
        private readonly ContentVersionRepositoryInterface $contentVersionRepository
    ) {}

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