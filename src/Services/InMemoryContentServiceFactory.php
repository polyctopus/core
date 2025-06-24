<?php

namespace Polyctopus\Core\Services;

use Polyctopus\Core\Services\ContentService;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentTypeRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVariantRepository;
use Polyctopus\Core\Repositories\InMemory\InMemoryContentVersionRepository;

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
            new InMemoryContentVariantRepository()
        );
    }
}