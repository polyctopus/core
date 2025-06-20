<?php
namespace Polyctopus\Core\Models\FieldTypes;

interface FieldTypeInterface
{
    /**
     * Validate a value for this field type.
     */
    public function validate($value, array $settings = []): bool;
}