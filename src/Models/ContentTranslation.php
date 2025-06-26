<?php
namespace Polyctopus\Core\Models;

use DateTimeImmutable;

class ContentTranslation
{
    public function __construct(
        public readonly string $id,
        public readonly string $entityType, 
        public readonly string $entityId,
        public readonly string $locale, 
        public array $fields = [],
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt ??= new DateTimeImmutable();
        $this->updatedAt ??= new DateTimeImmutable();
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}