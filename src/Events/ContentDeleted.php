<?php

namespace Polyctopus\Core\Events;

class ContentDeleted implements EventInterface
{
    public function __construct(
        public string $id,
        protected \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {}

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