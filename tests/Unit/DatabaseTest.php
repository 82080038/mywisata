<?php

/**
 * Database Unit Test
 *
 * Unit tests for Database class.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * Test database connection
     */
    public function testDatabaseConnection()
    {
        $db = Database::getInstance();
        $this->assertInstanceOf(PDO::class, $db->getConnection());
    }

    /**
     * Test singleton pattern
     */
    public function testSingletonPattern()
    {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2);
    }
}
