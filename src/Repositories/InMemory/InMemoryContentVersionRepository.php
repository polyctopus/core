<?php

namespace Polyctopus\Core\Repositories\InMemory;

use Polyctopus\Core\Repositories\ContentVersionRepositoryInterface;

class InMemoryContentVersionRepository implements ContentVersionRepositoryInterface
{
    private array $versions = [];

    public function findByEntity(string $entityType, string $entityId): array
    {
        return array_filter($this->versions, function ($version) use ($entityType, $entityId) {
            return $version->getEntityType() === $entityType && $version->getEntityId() === $entityId;
        });
    }

    public function save($version): void
    {
        $this->versions[$version->getId()] = $version;
    }

    public function delete(string $id): void
    {
        unset($this->versions[$id]);
    }
    
    public function all(): array
    {
        return $this->versions;
    }
    
}

