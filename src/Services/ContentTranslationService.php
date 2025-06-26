<?php
namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\ContentTranslation;
use Polyctopus\Core\Repositories\ContentTranslationRepositoryInterface;

final class ContentTranslationService
{
    public function __construct(
        private readonly ContentTranslationRepositoryInterface $contentTranslationRepository
    ) {}

    public function addOrUpdateTranslation(
        string $entityType,
        string $entityId,
        string $locale,
        array $fields
    ): void {
        $existing = $this->contentTranslationRepository->findByEntityAndLocale($entityType, $entityId, $locale);
        $translation = new ContentTranslation(
            $existing?->id ?? uniqid('trans_', true),
            $entityType,
            $entityId,
            $locale,
            $fields
        );
        $this->contentTranslationRepository->save($translation);
    }

    public function getTranslation(
        string $entityType,
        string $entityId,
        string $locale
    ): ?ContentTranslation {
        return $this->contentTranslationRepository->findByEntityAndLocale($entityType, $entityId, $locale);
    }
}