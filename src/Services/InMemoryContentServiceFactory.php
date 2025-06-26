<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Services\ContentService;
use Polyctopus\Core\Events\EventInterface;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVariantRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVersionRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTranslationRepository;

class InMemoryContentServiceFactory
{
    /**
     * Creates an instance of ContentService with in-memory repositories.
     *
     * @return ContentService
     */
    public static function create(): ContentService
    {
        return  new ContentService(
            new InMemoryContentRepository(), 
            new InMemoryContentTypeRepository(),
            new InMemoryContentVersionRepository(),
            new InMemoryContentVariantRepository(),
            new InMemoryContentTranslationRepository(),
             function(EventInterface $event) {
                echo $event->getName() . " event dispatched at " . $event->getTimestamp()->format('Y-m-d H:i:s') . "\n";
            
            }
        );
    }
}