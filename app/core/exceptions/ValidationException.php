<?php

/**
 * MyWisata Application - Validation Exception
 *
 * Thrown when validation fails.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class ValidationException extends RuntimeException
{
    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors;

    /**
     * Constructor
     *
     * @param array $errors Validation errors
     * @param string $message Exception message
     * @param int $code Exception code
     */
    public function __construct(array $errors, $message = 'Validation failed', $code = 422)
    {
        $this->errors = $errors;
        parent::__construct($message, $code);
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
