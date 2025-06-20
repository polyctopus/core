<?php

namespace Polyctopus\Core\Models\FieldTypes;

class TextFieldType implements FieldTypeInterface
{
    public function validate($value, array $settings = []): bool
    {
        if (isset($settings['maxLength']) && strlen($value) > $settings['maxLength']) {
            return false;
        }
        // Add more validation as needed
        return is_string($value);
    }
}