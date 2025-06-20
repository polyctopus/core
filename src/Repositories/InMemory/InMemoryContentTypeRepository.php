<?php
namespace Polyctopus\Core\Repositories\InMemory;

use Polyctopus\Core\Models\ContentType;
use Polyctopus\Core\Repositories\ContentTypeRepositoryInterface;

class InMemoryContentTypeRepository implements ContentTypeRepositoryInterface
{
    private array $storage = [];

    public function all(): array
    {
        return array_values($this->storage);
    }

    public function find(string $id): ?ContentType
    {
        return $this->storage[$id] ?? null;
    }

    public function save(ContentType $contentType): void
    {
        $this->storage[$contentType->getId()] = $contentType;
    }

    public function delete(string $id): void
    {
        unset($this->storage[$id]);
    }
}
