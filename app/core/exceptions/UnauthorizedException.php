<?php

/**
 * MyWisata Application - Unauthorized Exception
 *
 * Thrown when user is not authorized to perform an action.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class UnauthorizedException extends RuntimeException
{
    /**
     * Constructor
     *
     * @param string $message Exception message
     * @param int $code Exception code
     */
    public function __construct($message = 'Unauthorized access', $code = 401)
    {
        parent::__construct($message, $code);
    }
}
