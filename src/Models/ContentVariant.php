<?php

namespace Polyctopus\Core\Models;

use DateTimeImmutable;

class ContentVariant
{
    public function __construct(
        public readonly string $id,
        public readonly string $contentId,
        public readonly string $dimension, // z.B. "brand_a", "brand_b", "campaign_x"
        public array $overrides = [],
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt ??= new DateTimeImmutable();
        $this->updatedAt ??= new DateTimeImmutable();
    }

    public function getOverrides(): array
    {
        return $this->overrides;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentId(): string
    {
        return $this->contentId;
    }

    public function getDimension(): string
    {
        return $this->dimension;
    }
}