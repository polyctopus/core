# Event Mechanism

Polyctopus Core provides a simple, framework-agnostic event mechanism.  
You can register event listeners (subscribers) for specific events and react to them in your application logic.  
For example, whenever new content is created, a `ContentCreated` event is dispatched.  
You can use this to trigger custom logic such as logging, notifications, or integrations.

**How it works:**
- Pass a callback (event dispatcher) to the `ContentService` constructor or use a dispatcher implementation.
- Register listeners for specific event types (e.g. `ContentCreated`).
- When an event occurs, all registered listeners for that event type are called with the event object.

**Example:**
```php
$service = InMemoryContentServiceFactory::create();

// Register a listener for content creation events
$service->setEventDispatcher(function($event) {
    if ($event instanceof \Polyctopus\Core\Events\ContentCreated) {
        echo "Content created: " . $event->getPayload()['content']->getId() . PHP_EOL;
    }
});
```
This mechanism is lightweight, easy to extend, and does not depend on any external framework.