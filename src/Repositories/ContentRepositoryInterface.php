<?php

namespace Polysync\Core\Repositories;

use Polysync\Core\Models\Content;

interface ContentRepositoryInterface
{
    /** @return Content[] */
    public function all(): array;
    public function find(string $id): ?Content;
    public function save(Content $content): void;
    public function delete(string $id): void;
}