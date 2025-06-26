<?php

namespace Polyctopus\Core\Events;

use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentVersion;

class ContentRolledBack implements EventInterface
{
    public function __construct(
        public Content $content,
        public ContentVersion $version,
        protected \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {}

    public function getName(): string
    {
        return 'content.rolled_back';
    }

    public function getPayload(): array
    {
        return [
            'content' => $this->content,
            'version' => $this->version,
        ];
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }
}