<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Models\Asset;
use Polyctopus\Core\Repositories\AssetRepositoryInterface;

final class AssetService
{
    public function __construct(
        private readonly AssetRepositoryInterface $assetRepository
    ) {}

    public function uploadAsset(Asset $asset, string $fileContent): void
    {
        $this->assetRepository->save($asset, $fileContent);
    }

    public function findAsset(string $id): ?Asset
    {
        return $this->assetRepository->find($id);
    }


    public function deleteAsset(string $id): void
    {
        $this->assetRepository->delete($id);
    }

    public function getAssetStream(string $id)
    {
        return $this->assetRepository->getStream($id);
    }
}