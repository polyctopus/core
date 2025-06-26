<?php
namespace Polyctopus\Core\Repositories;

use Polyctopus\Core\Models\ContentTranslation;

interface ContentTranslationRepositoryInterface
{
    public function findByEntityAndLocale(string $entityType, string $entityId, string $locale): ?ContentTranslation;
    public function save(ContentTranslation $translation): void;
    public function delete(string $id): void;
}