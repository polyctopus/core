<?php

namespace Polyctopus\Core\Events;

use Polyctopus\Core\Models\Content;

class ContentCreated implements EventInterface
{
    public Content $content;
    protected \DateTimeImmutable $timestamp;

    public function __construct(Content $content) {
        $this->content = $content;
        $this->timestamp = new \DateTimeImmutable();
    }

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