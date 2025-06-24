<?php

namespace Polyctopus\Core\Repositories\InMemory;

use Polyctopus\Core\Repositories\ContentVariantRepositoryInterface;
use Polyctopus\Core\Models\ContentVariant;

class InMemoryContentVariantRepository implements ContentVariantRepositoryInterface
{
    private array $variants = [];

    public function findByContentAndDimension(string $contentId, string $dimension): ?ContentVariant
    {
        foreach ($this->variants as $variant) {
            if ($variant->getContentId() === $contentId && $variant->getDimension() === $dimension) {
                return $variant;
            }
        }
        return null;
    }

    public function save(ContentVariant $variant): void
    {
        $this->variants[$variant->getId()] = $variant;
    }

    public function delete(string $id): void
    {
        unset($this->variants[$id]);
    }
}