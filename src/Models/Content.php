<?php

namespace Polysync\Core\Models;

use DateTimeImmutable;

class Content
{
    public function __construct(
        private string $id,
        private ContentType $contentType,
        private ContentStatus $status,
        private array $data = [],
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $createdAt = null,

    ) {
        $this->id = $id;
        $this->contentType = $contentType;
        $this->data = $data;
        $this->status = $status;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
        $this->touch();
    }

    public function getStatus(): ContentStatus
    {
        return $this->status;
    }

    public function setStatus(ContentStatus $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contentType' => $this->contentType,
            'data' => $this->data,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['contentType'],
            $data['status'] ?? $data['status'],
            $data['data'] ?? [],
            isset($data['updatedAt']) ? new DateTimeImmutable($data['updatedAt']) : null,
            isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null
        );
    }
}
