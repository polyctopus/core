<?php

namespace Polyctopus\Core\Events;

use Polyctopus\Core\Models\Content;
use Polyctopus\Core\Models\ContentVersion;

class ContentRolledBack implements EventInterface
{
    public Content $content;
    public ContentVersion $version;
    protected \DateTimeImmutable $timestamp;

    public function __construct(Content $content, ContentVersion $version) {
        $this->content = $content;
        $this->version = $version;
        $this->timestamp = new \DateTimeImmutable();
    }

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