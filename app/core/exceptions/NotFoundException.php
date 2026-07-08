<?php

/**
 * MyWisata Application - Not Found Exception
 *
 * Thrown when a resource is not found.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class NotFoundException extends RuntimeException
{
    /**
     * Constructor
     *
     * @param string $resource Resource name
     * @param int $code Exception code
     */
    public function __construct($resource = 'Resource', $code = 404)
    {
        parent::__construct("{$resource} tidak ditemukan", $code);
    }
}
