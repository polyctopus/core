<?php
namespace Polysync\Core\Repositories;

use Polysync\Core\Models\ContentType;

interface ContentTypeRepositoryInterface
{
    public function all(): array;
    public function find(string $id): ?ContentType;
    public function save(ContentType $contentType): void;
    public function delete(string $id): void;
}