<?php

use Polyctopus\Core\Models\Asset;
use Polyctopus\Core\Services\AssetService;
use Polyctopus\Core\Repositories\AssetRepositoryInterface;

beforeEach(function () {
    // Mock Repository
    $this->repo = new class implements AssetRepositoryInterface {
        public array $assets = [];
        public array $streams = [];
        public function save(Asset $asset, string $fileContent): void
        {
            $this->assets[$asset->id] = $asset;
            $this->streams[$asset->id] = fopen('php://memory', 'r+');
            fwrite($this->streams[$asset->id], $fileContent);
            rewind($this->streams[$asset->id]);
        }
        public function find(string $id): ?Asset
        {
            return $this->assets[$id] ?? null;
        }
        public function delete(string $id): void
        {
            unset($this->assets[$id]);
            if (isset($this->streams[$id])) {
                fclose($this->streams[$id]);
                unset($this->streams[$id]);
            }
        }
        public function getStream(string $id)
        {
            return $this->streams[$id] ?? null;
        }
    };
    $this->service = new AssetService($this->repo);
});

it('can upload and find an asset', function () {
    $asset = new Asset(
        id: 'a1',
        filename: 'test.txt',
        mimeType: 'text/plain',
        size: 4,
        storagePath: '/tmp/test.txt'
    );
    $this->service->uploadAsset($asset, 'test');
    $found = $this->service->findAsset('a1');
    expect($found)->not()->toBeNull();
    expect($found->filename)->toBe('test.txt');
});

it('can delete an asset', function () {
    $asset = new Asset(
        id: 'a2',
        filename: 'del.txt',
        mimeType: 'text/plain',
        size: 3,
        storagePath: '/tmp/del.txt'
    );
    $this->service->uploadAsset($asset, 'del');
    $this->service->deleteAsset('a2');
    expect($this->service->findAsset('a2'))->toBeNull();
});

it('can get an asset stream', function () {
    $asset = new Asset(
        id: 'a3',
        filename: 'stream.txt',
        mimeType: 'text/plain',
        size: 6,
        storagePath: '/tmp/stream.txt'
    );
    $this->service->uploadAsset($asset, 'stream');
    $stream = $this->service->getAssetStream('a3');
    expect($stream)->not()->toBeNull();
    expect(stream_get_contents($stream))->toBe('stream');
});