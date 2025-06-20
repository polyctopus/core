<?php
namespace Polysync\Core\Repositories\InMemory;

use Polysync\Core\Models\ContentType;
use Polysync\Core\Repositories\ContentTypeRepositoryInterface;

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
