<?php

namespace Polyctopus\Core\Models;

use DateTimeImmutable;

class ContentVersion
{
    private string            $id;
    private string            $entityType;   
    private string            $entityId;    
    private array             $snapshot;   
    private ?string           $diff;    
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $entityType,
        string $entityId,
        array  $snapshot,
        ?string $diff = null,
        ?DateTimeImmutable $createdAt = null
    ) {
        $this->id         = $id;
        $this->entityType = $entityType;
        $this->entityId   = $entityId;
        $this->snapshot   = $snapshot;
        $this->diff       = $diff;
        $this->createdAt  = $createdAt ?? new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'entityType' => $this->entityType,
            'entityId'   => $this->entityId,
            'snapshot'   => $this->snapshot,
            'diff'       => $this->diff,
            'createdAt'  => $this->createdAt->format(DATE_ATOM),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['entityType'],
            $data['entityId'],
            $data['snapshot'],
            $data['diff'] ?? null,
            new DateTimeImmutable($data['createdAt'])
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getSnapshot(): array
    {
        return $this->snapshot;
    }

    public function getDiff(): ?string
    {
        return $this->diff;
    }
}