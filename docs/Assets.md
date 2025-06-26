# Asset Handling

Polyctopus Core provides a flexible way to manage and reference assets (such as images, documents, or other files) in your content.  
Assets are managed independently from content and can be linked to any content entry by storing their IDs in your content data.

### How it works

- **Asset Model:** An `Asset` represents a file with metadata (filename, MIME type, size, storage path, etc.).
- **Asset Repository Interface:** The `AssetRepositoryInterface` defines how assets are stored, retrieved, and deleted. You can implement this interface for local storage, cloud storage (e.g. S3), or in-memory/testing.
- **Asset Service:** The `AssetService` provides methods to upload, find, delete, and stream assets.

### Linking Assets to Content

You reference assets in your content by storing their IDs in the content data array.  
For example, a content entry with an image and a gallery might look like this:

```php
$data = [
    'title' => 'Example',
    'imageId' => 'asset_123',                // Single asset reference
    'gallery' => ['asset_123', 'asset_456'], // Multiple asset references
];
$content = $service->createContent('c1', $contentType, $data);
```

To retrieve the actual asset objects, use the `AssetService`:

```php
$imageAsset = $assetService->findAsset($content->getData()['imageId']);
foreach ($content->getData()['gallery'] as $assetId) {
    $galleryAsset = $assetService->findAsset($assetId);
    // ...process asset
}
```

### Uploading and Managing Assets

Assets are uploaded and managed via the `AssetService`:

```php
use Polyctopus\Core\Models\Asset;

// Create an Asset object (metadata)
$asset = new Asset(
    id: 'asset_123',
    filename: 'picture.jpg',
    mimeType: 'image/jpeg',
    size: 123456,
    storagePath: '/uploads/picture.jpg'
);

// Upload the asset with file content
$assetService->uploadAsset($asset, $fileContent);

// Delete an asset
$assetService->deleteAsset('asset_123');

// Get a stream for download or processing
$stream = $assetService->getAssetStream('asset_123');
```

### Implementation Note

Polyctopus Core only defines the interface for asset storage (`AssetRepositoryInterface`).  
You are free to implement this interface for your preferred storage backend (filesystem, cloud, etc.).  
This keeps your application flexible and decoupled from any specific storage technology.

**Best Practice:**  
Store only the asset IDs in your content.  
Use the `AssetService` to resolve asset metadata and file content when needed.

---
