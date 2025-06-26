<?php

namespace Polyctopus\Core\Events;

use Polyctopus\Core\Models\Content;

class ContentCreated implements EventInterface
{
    public function __construct(
        public readonly Content $content,
        private readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {}

    public function getName(): string
    {
        return 'content.created';
    }

    public function getPayload(): array
    {
        return ['content' => $this->content];
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }
}