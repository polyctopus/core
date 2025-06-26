<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Events\EventInterface;

trait EventDispatcherTrait
{
    private $eventDispatcher = null;

    private function dispatch(EventInterface $event): void
    {
        if ($this->eventDispatcher) {
            ($this->eventDispatcher)($event);
        }
    }

    public function setEventDispatcher(callable $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}