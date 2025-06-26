<?php
namespace Polyctopus\Core\Models;

use DateTimeImmutable;

class Asset
{
    public function __construct(
        public readonly string $id,
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $storagePath, // z.B. Pfad oder URL
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
        public readonly ?array $meta = null // z.B. width, height, duration, etc.
    ) {}
}