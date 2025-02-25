<?php

declare(strict_types=1);

namespace Istrelka\Storage\Tests;

use Istrelka\Storage\StorageContext;
use Istrelka\Storage\Strategy\MySQLStorage;

trait RefreshDatabase
{
    public function setUp(): void
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $connection = $storage->getStorage()->connect()->getConnection();

        $tableName = MySqlStorageTest::TEST_TABLE_NAME;

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
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $connection = $storage->getStorage()->connect()->getConnection();

        $tableName = MySqlStorageTest::TEST_TABLE_NAME;

        $sql = "DROP TABLE IF EXISTS {$tableName};";

        $connection->exec($sql);
    }
}