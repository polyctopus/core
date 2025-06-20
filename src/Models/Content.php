<?php

namespace Polysync\Core\Models;

use DateTimeImmutable;

class Content
{
    private string $id;
    private ContentType $contentType;
    private array $data;            // key => value for master fields
    private string $status;         // 'draft'|'published'
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        ContentType $contentType,
        array $data = [],
        string $status = 'draft',
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->contentType = $contentType;
        $this->data = $data;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
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
        // You need to resolve ContentType instance from $data['contentType'] or $data['contentTypeId']
        // Here, we assume $data['contentType'] is already a ContentType instance
        return new self(
            $data['id'],
            $data['contentType'],
            $data['data'] ?? [],
            $data['status'] ?? 'draft',
            isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            isset($data['updatedAt']) ? new DateTimeImmutable($data['updatedAt']) : null
        );
    }
}
