<?php

namespace Polyctopus\Core\Factories;

use Polyctopus\Core\{
    Repositories\InMemory\InMemoryContentRepository,
    Repositories\InMemory\InMemoryContentTypeRepository,
    Repositories\InMemory\InMemoryContentVariantRepository,
    Repositories\InMemory\InMemoryContentTranslationRepository,
    Repositories\InMemory\InMemoryContentVersionRepository,
    Services\ContentService,
    Services\ContentTypeService,    
    Services\ContentVariantService,
    Services\ContentTranslationService,
    Services\ContentVersionService,
    Events\EventInterface
};

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
             new ContentTypeService(
                new InMemoryContentTypeRepository()
             ),
             new ContentVariantService(
                new InMemoryContentVariantRepository()
            ),    
            new ContentTranslationService(
                new InMemoryContentTranslationRepository()
            ), 
            new ContentVersionService(
                new InMemoryContentVersionRepository()
            ),        
             function(EventInterface $event) {
                       
            }
        );
    }
}