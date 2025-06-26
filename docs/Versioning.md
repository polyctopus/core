# Versioning & Rollback

- **Automatic versioning:**  
  Every time you create or update content using the service, a new version is created and stored in the `ContentVersionRepositoryInterface`.
- **Rollback:**  
  You can access all versions for a content entry and use the `rollback($contentId, $versionId)` method of the service to restore a previous state.

**Example:**
```php
// List all versions for a content entry
$versions = $service->contentVersionService->findContentVersionsByEntityType('content', 'c1');
foreach ($versions as $version) {
    echo "- Version ID: {$version->getId()}, Snapshot: " . json_encode($version->toArray()['snapshot']) . PHP_EOL;
}

// Rollback to the first version
$firstVersion = reset($versions);
if ($firstVersion) {
    $service->rollback('c1', $firstVersion->getId());
}
```
