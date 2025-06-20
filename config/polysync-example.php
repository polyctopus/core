return [
    // Repository bindings (class names) for each interface
    'repositories' => [
        'content_type'      => Polysync\Core\Repositories\InMemory\InMemoryContentTypeRepository::class,
        'content_field'     => Polysync\Core\Repositories\InMemory\InMemoryContentFieldRepository::class,
        'content'           => Polysync\Core\Repositories\InMemory\InMemoryContentRepository::class,
        'variant'           => Polysync\Core\Repositories\InMemory\InMemoryVariantRepository::class,
        'dimension'         => Polysync\Core\Repositories\InMemory\InMemoryDimensionRepository::class,
        'dimension_value'   => Polysync\Core\Repositories\InMemory\InMemoryDimensionValueRepository::class,
    ],
];
