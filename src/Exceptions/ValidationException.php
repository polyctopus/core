<?php

namespace Polyctopus\Core\Exceptions;

use Exception;

class ValidationException extends Exception
{
    /** @var ValidationError[] */
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct("Validation failed");
        $this->errors = $errors;
    }

    /** @return ValidationError[] */
    public function getErrors(): array
    {
        return $this->errors;
    }
}