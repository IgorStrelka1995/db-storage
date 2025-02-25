<?php

declare(strict_types=1);

namespace Istrelka\Storage\Contract;

use Istrelka\Storage\Exception\ConnectionException;
use Istrelka\Storage\Exception\ParameterException;

interface StorageInterface
{
    /**
     * @return StorageInterface
     */
    public function connect(): StorageInterface;

    /**
     * @param string $tableName
     * @param array $columns Format of the columns ['column1', 'column2']
     * @param int $mode
     * @return array
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function findAll(string $tableName, array $columns = [], int $mode = \PDO::FETCH_ASSOC): array;

    /**
     * @param string $tableName
     * @param array $condition Format of the condition ['column' => 'value']
     * @param array $columns Format of the columns ['column1', 'column2']
     * @return array
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function findOne(string $tableName, array $condition, array $columns = []): array;

    /**
     * @param string $tableName
     * @param array $conditions Format of the conditions [['column', 'equal', 'value], ['column', 'equal' 'value']]
     * @param array $columns Format of the columns ['column1', 'column2']
     * @return array
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function find(string $tableName, array $conditions, array $columns = []): array;

    /**
     * @param string $tableName
     * @param array $insertData Format of the insert data ['column' => 'value']
     * @return bool
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function insert(string $tableName, array $insertData): bool;

    /**
     * @param string $tableName
     * @param array $updatedData Format of the update data ['column' => 'value']
     * @param array $conditions Format of the conditions [['column', 'equal', 'value], ['column', 'equal' 'value']]
     * @return bool
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function update(string $tableName, array $updatedData, array $conditions): bool;

    /**
     * @param string $tableName
     * @param array $condition Format of the condition ['column' => 'value']
     * @return bool
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function delete(string $tableName, array $condition): bool;
}