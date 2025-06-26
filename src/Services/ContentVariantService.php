<?php
namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\ContentVariant;
use Polyctopus\Core\Repositories\ContentVariantRepositoryInterface;

final class ContentVariantService
{
    public function __construct(
        private readonly ContentVariantRepositoryInterface $contentVariantRepository
    ) {}

    public function createContentVariant(ContentVariant $variant): void
    {
        $this->contentVariantRepository->save($variant);
    }

    public function deleteContentVariant(string $variantId): void
    {
        $this->contentVariantRepository->delete($variantId);
    }

    public function findContentVariant(string $contentId, string $dimension): ?ContentVariant
    {
        return $this->contentVariantRepository->findByContentAndDimension($contentId, $dimension);
    }
}