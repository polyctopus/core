<?php

namespace Polyctopus\Core\Repositories;

use Polyctopus\Core\Models\Asset;

interface AssetRepositoryInterface
{
    public function save(Asset $asset, string $fileContent): void;
    public function find(string $id): ?Asset;
    public function delete(string $id): void;
    /**
     * @param string $id
     * @return resource|null
     */
    public function getStream(string $id);
    // ggf. weitere Methoden
}