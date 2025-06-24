<?php

namespace Polyctopus\Core\Repositories;

use Polyctopus\Core\Models\ContentVariant;

interface ContentVariantRepositoryInterface
{
    public function findByContentAndDimension(string $contentId, string $dimension): ?ContentVariant;
    public function save(ContentVariant $variant): void;
    public function delete(string $id): void;
}