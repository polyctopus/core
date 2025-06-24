<?php
namespace Polyctopus\Core\Repositories;

use Polyctopus\Core\Models\ContentVersion;

interface ContentVersionRepositoryInterface
{
    /** @return Version[] */
    public function findByEntity(string $entityType, string $entityId): array;
    public function save(ContentVersion $version): void;
    public function all(): array;
}