<?php
// src/Models/ContentField.php
namespace Polysync\Core\Models;

use DateTimeImmutable;
use Polysync\Core\Models\FieldTypes\FieldTypeInterface;

/**
 * Represents a field in a content type.
 * Fields can have different types (e.g., text, number, date) and settings.
 */
class ContentField
{
    public const FIELD_TYPE_TEXT = 'Text';

    private string $id;
    private string $contentTypeId;
    private string $code;
    private string $label;
    private FieldTypeInterface $fieldType;
    private array $settings;
    private int $sortOrder;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $contentTypeId,
        string $code,
        string $label,
        FieldTypeInterface $fieldType,
        array $settings = [],
        int $sortOrder = 0,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->contentTypeId = $contentTypeId;
        $this->code = $code;
        $this->label = $label;
        $this->fieldType = $fieldType;
        $this->settings = $settings;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentTypeId(): string
    {
        return $this->contentTypeId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getFieldType(): FieldTypeInterface
    {
        return $this->fieldType;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public static function getAllowedFieldTypes(): array
    {
        return [
            self::FIELD_TYPE_TEXT,
            // Add more types here as you define them
        ];
    }

    public function validate($value): bool
    {
       return $this->fieldType->validate($value, $this->settings);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
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

    // Helper-Methode:
    private function getFieldTypeName(): string
    {
        return match (get_class($this->fieldType)) {
            \Polysync\Core\Models\FieldTypes\TextFieldType::class => self::FIELD_TYPE_TEXT,
            // weitere Typen hier ergänzen
            default => throw new \InvalidArgumentException('Unknown field type class'),
        };
    }

    public static function fromArray(array $data): self
    {
        // Factory für FieldType-Objekte
        $fieldType = match ($data['fieldType']) {
            self::FIELD_TYPE_TEXT => new \Polysync\Core\Models\FieldTypes\TextFieldType(),
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