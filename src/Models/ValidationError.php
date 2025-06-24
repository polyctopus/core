<?php

namespace Polyctopus\Core\Models;

class ValidationError
{
    public function __construct(
        public readonly string $field,
        public readonly mixed $value,
        public readonly string $message
    ) {}
}