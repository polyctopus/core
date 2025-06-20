<?php

namespace Polysync\Core\Repositories\InMemory;

use Polysync\Core\Models\Content;
use Polysync\Core\Repositories\ContentRepositoryInterface;

class InMemoryContentRepository implements ContentRepositoryInterface
{
    /** @var Content[] */
    private array $storage = [];

    public function all(): array
    {
        return array_values($this->storage);
    }

    public function find(string $id): ?Content
    {
        return $this->storage[$id] ?? null;
    }

    public function save(Content $content): void
    {
        $this->storage[$content->getId()] = $content;
    }

    public function delete(string $id): void
    {
        unset($this->storage[$id]);
    }
}