<?php

declare(strict_types=1);

namespace Istrelka\Storage\Tests;

use Istrelka\Storage\Exception\ParameterException;
use Istrelka\Storage\Exception\QueryException;
use Istrelka\Storage\StorageContext;
use Istrelka\Storage\Strategy\MySQLStorage;
use PHPUnit\Framework\TestCase;

class MySqlStorageTest extends TestCase
{
    const TEST_TABLE_NAME = 'test_table';

    use RefreshDatabase;

    /**
     * @throws \Exception
     */
    public function testInsert()
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        try {
            $storage->getStorage()->insert('', ['name' => 'John']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertEquals("Please, provide a name of database table.", $e->getMessage());
        }

        try {
            $storage->getStorage()->insert(self::TEST_TABLE_NAME, []);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertEquals("Please, provide data for insert query.", $e->getMessage());
        }

        try {
            $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['names' => 'John']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Atable test_table has no column named names%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->insert('test_tabled', ['names' => 'John']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such table: test_tabled%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->insert('test_tabled', ['name']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%AThe format of insert data is wrong.%A", $e->getMessage());
        }

        $result = $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);

        $this->assertTrue($result);
    }

    /**
     * @throws \Exception
     */
    public function testUpdate()
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Peter']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Jack']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);

        try {
            $storage->getStorage()->update('', ['name' => 'John Updated'], [['name', '=', 'John'], ['id', '>', 1]]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide a name of database table.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->update(self::TEST_TABLE_NAME, [], [['name', '=', 'John'], ['id', '>', 1]]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide data for update query.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->update(self::TEST_TABLE_NAME, ['name' => 'John Updated'], []);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide condition values.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->update(self::TEST_TABLE_NAME, ['named' => 'John Updated'], [['name', '=', 'John'], ['id', '>', 1]]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such column: named%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->update(self::TEST_TABLE_NAME, ['name' => 'John Updated'], [['name', '=', 'John'], ['ids', '>', 1]]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such column: ids%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->update(self::TEST_TABLE_NAME, ['name'], [['name', '=', 'John'], ['ids', '>', 1]]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%AThe format of update data is wrong.%A", $e->getMessage());
        }

        $result = $storage->getStorage()->update(self::TEST_TABLE_NAME, ['name' => 'John Updated'], [['name', '=', 'John'], ['id', '>', 1]]);

        $this->assertTrue($result);

        $data = $storage->getStorage()->findOne(self::TEST_TABLE_NAME, ['id' => 4]);

        $this->assertEquals('John Updated', $data['name']);
    }

    /**
     * @throws \Exception
     */
    public function testDelete()
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Peter']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Jack']);

        $data = $storage->getStorage()->findAll(self::TEST_TABLE_NAME);

        $this->assertCount(3, $data);

        try {
            $storage->getStorage()->delete('', ['id' => 1]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertEquals("Please, provide a name of database table.", $e->getMessage());
        }

        try {
            $storage->getStorage()->delete(self::TEST_TABLE_NAME, []);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertEquals("Please, provide condition values.", $e->getMessage());
        }

        try {
            $storage->getStorage()->delete(self::TEST_TABLE_NAME, ['ids' => 1]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such column: ids%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->delete(self::TEST_TABLE_NAME, ['id']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%AThe format of delete data is wrong.%A", $e->getMessage());
        }

        $storage->getStorage()->delete(self::TEST_TABLE_NAME, ['id' => 1]);

        $data = $storage->getStorage()->findAll(self::TEST_TABLE_NAME);

        $this->assertCount(2, $data);
    }

    /**
     * @throws \Exception
     */
    public function testFindAll()
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Peter']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Jack']);

        try {
            $storage->getStorage()->findAll('', ['id', 'name']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide a name of database table.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->findAll(self::TEST_TABLE_NAME, ['id', 'named']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such column: named%A", $e->getMessage());
        }

        $data = $storage->getStorage()->findAll(self::TEST_TABLE_NAME, ['id', 'name']);

        $this->assertNotEmpty($data);

        $this->assertCount(3, $data);

        $this->assertEquals('John', $data[0]['name']);
    }

    public function testFindOne()
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Peter']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Jack']);

        try {
            $storage->getStorage()->findOne('', ['id', 'name']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide a name of database table.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->findOne(self::TEST_TABLE_NAME, []);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide condition values.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->findOne(self::TEST_TABLE_NAME, ['names' => 'John']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such column: names%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->findOne(self::TEST_TABLE_NAME, ['name']);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%AThe format of condition data is wrong.%A", $e->getMessage());
        }

        $data = $storage->getStorage()->findOne(self::TEST_TABLE_NAME, ['name' => 'John']);

        $this->assertNotEmpty($data);

        $this->assertEquals('John', $data['name']);
    }

    public function testFind()
    {
        $mysqlStorage = new MySQLStorage();

        $storage = new StorageContext($mysqlStorage);

        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Peter']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'Jack']);
        $storage->getStorage()->insert(self::TEST_TABLE_NAME, ['name' => 'John']);

        try {
            $storage->getStorage()->find('', [
                ['name', '=', 'John'],
                ['id', '>', 1]
            ]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide a name of database table.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->find(self::TEST_TABLE_NAME, []);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (ParameterException $e) {
            $this->assertStringMatchesFormat("%APlease, provide condition values.%A", $e->getMessage());
        }

        try {
            $storage->getStorage()->find(self::TEST_TABLE_NAME, [
                ['names', '=', 'John'],
                ['id', '>', 1]
            ]);

            $this->assertTrue(false, 'The code should not get here.');
        } catch (QueryException $e) {
            $this->assertStringMatchesFormat("%Ano such column: names%A", $e->getMessage());
        }

        $data = $storage->getStorage()->find(self::TEST_TABLE_NAME, [
            ['name', '=', 'John'],
            ['id', '>', 1]
        ]);

        $this->assertNotEmpty($data);

        $this->assertCount(1, $data);
    }
}