# Publish Start & End Dates

Polyctopus Core supports scheduling content visibility using publish start and end dates (including time).  
This allows you to control exactly when a content entry should be visible or hidden.

## How it works

- Each `Content` object can have an optional `publishStart` and `publishEnd` date (both are `DateTimeImmutable`).
- If set, the content is considered "published" only if the current time is within this range.
- If not set, the content is always considered published (unless other status rules apply).

## Setting publish dates

```php
$content->setPublishStart(new DateTimeImmutable('2025-07-10 08:00:00'));
$content->setPublishEnd(new DateTimeImmutable('2025-07-20 23:59:59'));
```

You can also set or clear these dates when creating or updating content.

## Checking if content is currently published

Use the ContentService helper:

```php
if ($service->isContentCurrentlyPublished($content)) {
    // Content is visible/published
} else {
    // Content is not published (yet or anymore)
}
```

## Use Cases

- Schedule news articles, promotions, or landing pages for automatic publishing/unpublishing.
- Hide expired content automatically.
- Combine with status (e.g. Draft/Published) for flexible workflows.

## Notes

- If `publishStart` is `null`, the content is immediately eligible for publishing.
- If `publishEnd` is `null`, the content remains published indefinitely after `publishStart`.
- Both dates include time (to the second).

---
