<?php

namespace Polyctopus\Core\Models;

use DateTimeImmutable;
use Polyctopus\Core\Models\FieldTypes\FieldTypeInterface;

readonly final class ContentField
{
    public const string FIELD_TYPE_TEXT = 'Text';

    public function __construct(
        public readonly string $id,
        public readonly string $contentTypeId,
        public readonly string $code,
        public readonly string $label,
        public readonly FieldTypeInterface $fieldType,
        public readonly array $settings = [],
        public readonly int $sortOrder = 0,
        public readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        public DateTimeImmutable $updatedAt = new DateTimeImmutable()
    ) {}

    public function validate(mixed $value): bool
    {
        return $this->fieldType->validate($value, $this->settings);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contentTypeId' => $this->contentTypeId,
            'code' => $this->code,
            'label' => $this->label,
            'fieldType' => $this->getFieldTypeName(),
            'settings' => $this->settings,
            'sortOrder' => $this->sortOrder,
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }

    private function getFieldTypeName(): string
    {
        return match (get_class($this->fieldType)) {
            \Polyctopus\Core\Models\FieldTypes\TextFieldType::class => self::FIELD_TYPE_TEXT,
            // weitere Typen hier ergänzen
            default => throw new \InvalidArgumentException('Unknown field type class'),
        };
    }

    public static function fromArray(array $data): self
    {
        // Factory für FieldType-Objekte
        $fieldType = match ($data['fieldType']) {
            self::FIELD_TYPE_TEXT => new \Polyctopus\Core\Models\FieldTypes\TextFieldType(),
            default => throw new \InvalidArgumentException('Unknown field type'),
        };

        return new self(
            $data['id'],
            $data['contentTypeId'],
            $data['code'],
            $data['label'],
            $fieldType,
            $data['settings'] ?? [],
            $data['sortOrder'] ?? 0,
            isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null
        );
    }
}