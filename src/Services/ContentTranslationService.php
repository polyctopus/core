<?php
namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\ContentTranslation;
use Polyctopus\Core\Repositories\ContentTranslationRepositoryInterface;
use Polyctopus\Core\Models\EntityType;

final class ContentTranslationService
{
    public function __construct(
        private readonly ContentTranslationRepositoryInterface $contentTranslationRepository
    ) {}

    public function addOrUpdateTranslation(
        EntityType $entityType,
        string $entityId,
        string $locale,
        array $fields
    ): void {
        $existing = $this->contentTranslationRepository->findByEntityAndLocale($entityType->value, $entityId, $locale);
        $translation = new ContentTranslation(
            $existing?->id ?? uniqid('trans_', true),
            $entityType->value,
            $entityId,
            $locale,
            $fields
        );
        $this->contentTranslationRepository->save($translation);
    }

    public function getTranslation(
        EntityType $entityType,
        string $entityId,
        string $locale
    ): ?ContentTranslation {
        return $this->contentTranslationRepository->findByEntityAndLocale($entityType->value, $entityId, $locale);
    }
}