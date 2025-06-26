<?php
namespace Polyctopus\Core\Repositories\InMemory;

use Polyctopus\Core\Repositories\ContentTranslationRepositoryInterface;
use Polyctopus\Core\Models\ContentTranslation;

class InMemoryContentTranslationRepository implements ContentTranslationRepositoryInterface
{
    private array $translations = [];

    public function findByEntityAndLocale(string $entityType, string $entityId, string $locale): ?ContentTranslation
    {
        foreach ($this->translations as $translation) {
            if (
                $translation->entityType === $entityType &&
                $translation->entityId === $entityId &&
                $translation->locale === $locale
            ) {
                return $translation;
            }
        }
        return null;
    }

    public function save(ContentTranslation $translation): void
    {
        $this->translations[$translation->id] = $translation;
    }

    public function delete(string $id): void
    {
        unset($this->translations[$id]);
    }
}