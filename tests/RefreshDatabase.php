<?php

declare(strict_types=1);

namespace Istrelka\Storage\Tests;

use Istrelka\Storage\Storage;

trait RefreshDatabase
{
    public function setUp(): void
    {
        $storage = new Storage();

        $connection = $storage->connect()->getConnection();

        $tableName = StorageTest::TEST_TABLE_NAME;

        $sql = "
            CREATE TABLE IF NOT EXISTS {$tableName} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            );
        ";

        $connection->exec($sql);
    }

    public function tearDown(): void
    {
        $storage = new Storage();

        $connection = $storage->connect()->getConnection();

        $tableName = StorageTest::TEST_TABLE_NAME;

        $sql = "DROP TABLE IF EXISTS {$tableName};";

        $connection->exec($sql);
    }
}