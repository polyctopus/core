<?php
namespace Polyctopus\Core\Models;

use DateTimeImmutable;

class ContentType
{
    public function __construct(
        public readonly string $id,
        public string $code,
        public string $label,
        /** @var ContentField[] */
        public array $fields = [],
        public readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        public DateTimeImmutable $updatedAt = new DateTimeImmutable()
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->touch();
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
        $this->touch();
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Adds a new field to the content type and updates the modification timestamp.
     *
     * @param ContentField $field The field to add.
     */
    public function addField(ContentField $field): void
    {
        $this->fields[] = $field;
        $this->touch();
    }

    /**
     * Removes a field from the content type by its ID and updates the modification timestamp.
     *
     * @param string $fieldId The ID of the field to remove.
     */
    public function removeField(string $fieldId): void
    {
        $this->fields = array_filter(
            $this->fields,
            fn(ContentField $f) => $f->id !== $fieldId
        );
        $this->touch();
    }

    /**
     * Updates the modification timestamp to the current time.
     */
    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Converts the content type object to an associative array.
     *
     * @return array The content type as an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            // Convert each ContentField to an array as well
            'fields' => array_map(fn(ContentField $f) => $f->toArray(), $this->fields),
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }

    /**
     * Creates a ContentType object from an associative array.
     *
     * @param array $data The array containing content type data.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $fields = [];
        foreach ($data['fields'] ?? [] as $f) {
            $fields[] = ContentField::fromArray($f);
        }
        return new self(
            $data['id'],
            $data['code'],
            $data['label'],
            $fields,
            isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            isset($data['updatedAt']) ? new DateTimeImmutable($data['updatedAt']) : null
        );
    }
}
