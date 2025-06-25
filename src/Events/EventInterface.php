<?php

namespace Polyctopus\Core\Events;

interface EventInterface
{
    /**
     * Get the name of the event.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the payload of the event.
     *
     * @return array
     */
    public function getPayload(): array;

    /**
     * Get the timestamp of the event.
     *
     * @return \DateTimeImmutable
     */
    public function getTimestamp(): \DateTimeImmutable;
}