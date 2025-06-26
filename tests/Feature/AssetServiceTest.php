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

it('stores and retrieves asset metadata', function () {
    $asset = new Asset(
        id: 'a6',
        filename: 'meta.jpg',
        mimeType: 'image/jpeg',
        size: 100,
        storagePath: '/tmp/meta.jpg',
        meta: ['width' => 800, 'height' => 600]
    );
    $this->service->uploadAsset($asset, 'img');
    $found = $this->service->findAsset('a6');
    expect($found->meta)->toMatchArray(['width' => 800, 'height' => 600]);
});

it('can handle large file uploads', function () {
    $largeContent = str_repeat('A', 1024 * 1024); // 1 MB
    $asset = new Asset(
        id: 'a5',
        filename: 'large.bin',
        mimeType: 'application/octet-stream',
        size: strlen($largeContent),
        storagePath: '/tmp/large.bin'
    );
    $this->service->uploadAsset($asset, $largeContent);
    $stream = $this->service->getAssetStream('a5');
    expect(strlen(stream_get_contents($stream)))->toBe(1024 * 1024);
});

it('returns null for unknown asset', function () {
    expect($this->service->findAsset('doesnotexist'))->toBeNull();
    expect($this->service->getAssetStream('doesnotexist'))->toBeNull();
});

it('overwrites an existing asset with the same id', function () {
    $asset = new Asset(
        id: 'a4',
        filename: 'file.txt',
        mimeType: 'text/plain',
        size: 4,
        storagePath: '/tmp/file.txt'
    );
    $this->service->uploadAsset($asset, 'old');
    $this->service->uploadAsset($asset, 'new');
    $stream = $this->service->getAssetStream('a4');
    expect(stream_get_contents($stream))->toBe('new');
});

it('removes stream after asset deletion', function () {
    $asset = new Asset(
        id: 'a7',
        filename: 'delete.txt',
        mimeType: 'text/plain',
        size: 3,
        storagePath: '/tmp/delete.txt'
    );
    $this->service->uploadAsset($asset, 'del');
    $this->service->deleteAsset('a7');
    expect($this->service->getAssetStream('a7'))->toBeNull();
});