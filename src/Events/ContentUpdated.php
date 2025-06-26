<?php

namespace Polyctopus\Core\Events;

use Polyctopus\Core\Models\Content;

final class ContentUpdated implements EventInterface
{
    public function __construct(
        public Content $content,
        protected \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {}

    public function getName(): string
    {
        return 'content.updated';
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