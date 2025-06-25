<?php

namespace Polyctopus\Core\Events;

class ContentDeleted implements EventInterface
{
    public string $id;
    protected \DateTimeImmutable $timestamp;

    public function __construct(string $id) {
        $this->id = $id;
        $this->timestamp = new \DateTimeImmutable();
    }

    public function getName(): string
    {
        return 'content.deleted';
    }

    public function getPayload(): array
    {
        return ['id' => $this->id];
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }
}